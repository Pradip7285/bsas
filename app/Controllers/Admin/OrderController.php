<?php

namespace App\Controllers\Admin;

use App\Libraries\Notifier;
use App\Models\CustomerModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Traits\AdminGuard;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class OrderController extends Controller
{
    use AdminGuard;

    private const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    public function index()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $status = trim((string) $this->request->getGet('status'));
        $search = trim((string) $this->request->getGet('q'));

        $builder = (new OrderModel())->orderBy('created_at', 'DESC');

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $builder->where('status', $status);
        } else {
            $status = '';
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('order_number', $search)
                ->orLike('shipping_name', $search)
                ->orLike('shipping_phone', $search)
                ->groupEnd();
        }

        return view('admin/orders', [
            'orders'       => $builder->findAll(200),
            'statusFilter' => $status,
            'search'       => $search,
            'statuses'     => self::STATUSES,
        ]);
    }

    public function show(string $orderNumber)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $order = (new OrderModel())->findByOrderNumber($orderNumber);
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        $items    = (new OrderItemModel())->forOrder((int) $order['id']);
        $customer = (new CustomerModel())->find($order['customer_id']);

        return view('admin/order-detail', [
            'order'    => $order,
            'items'    => $items,
            'customer' => $customer,
            'statuses' => self::STATUSES,
        ]);
    }

    public function updateStatus(string $orderNumber)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $model = new OrderModel();
        $order = $model->findByOrderNumber($orderNumber);
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        $status = (string) $this->request->getPost('status');
        if (! in_array($status, self::STATUSES, true)) {
            return redirect()->to('/admin/orders/' . $orderNumber)->with('errors', ['Invalid status selected.']);
        }

        $model->transitionStatus((int) $order['id'], $status);

        $adminNote = trim((string) $this->request->getPost('admin_note'));
        if ($adminNote !== '') {
            $model->update($order['id'], ['admin_note' => $adminNote]);
        }

        $this->notifyStatusChange($model->find($order['id']));

        session()->setFlashdata('success', 'Order status updated.');

        return redirect()->to('/admin/orders/' . $orderNumber);
    }

    public function updateTracking(string $orderNumber)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $model = new OrderModel();
        $order = $model->findByOrderNumber($orderNumber);
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->update($order['id'], [
            'courier_name'    => trim((string) $this->request->getPost('courier_name')) ?: null,
            'tracking_number' => trim((string) $this->request->getPost('tracking_number')) ?: null,
            'tracking_url'    => trim((string) $this->request->getPost('tracking_url')) ?: null,
        ]);

        session()->setFlashdata('success', 'Tracking details updated.');

        return redirect()->to('/admin/orders/' . $orderNumber);
    }

    private function notifyStatusChange(array $order): void
    {
        $customer = (new CustomerModel())->find($order['customer_id']);
        if (! $customer) {
            return;
        }

        Notifier::sendCustomerEmail(
            $customer['email'],
            'Update on your BSAS order ' . $order['order_number'],
            "Your order {$order['order_number']} is now: " . ucfirst($order['status'])
                . ($order['tracking_number'] ? "\nTracking: {$order['courier_name']} — {$order['tracking_number']}" : '')
        );
    }
}
