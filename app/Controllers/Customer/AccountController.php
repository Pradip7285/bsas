<?php

namespace App\Controllers\Customer;

use App\Models\CustomerAddressModel;
use App\Models\CustomerModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Traits\RendersStorefrontPages;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class AccountController extends Controller
{
    use RendersStorefrontPages;

    public function dashboard()
    {
        $customerId = $this->customerId();
        $orderModel = new OrderModel();
        $orders     = $orderModel->forCustomer($customerId)->findAll(5);

        return $this->page('account/dashboard', 'My Account', [
            'customer'      => $this->currentCustomer(),
            'orders'        => $orders,
            'activeNav'     => 'dashboard',
            'orderCount'    => $orderModel->where('customer_id', $customerId)->countAllResults(),
            'pendingCount'  => $orderModel->where('customer_id', $customerId)->whereIn('status', ['pending', 'confirmed', 'processing'])->countAllResults(),
            'addressCount'  => (new CustomerAddressModel())->where('customer_id', $customerId)->countAllResults(),
        ]);
    }

    public function orders()
    {
        $status = trim((string) $this->request->getGet('status'));
        $builder = (new OrderModel())->forCustomer($this->customerId());
        if ($status !== '') {
            $builder->where('status', $status);
        }

        return $this->page('account/orders', 'My Orders', [
            'orders'     => $builder->findAll(),
            'statusFilter' => $status,
            'activeNav'  => 'orders',
        ]);
    }

    public function orderDetail(string $orderNumber)
    {
        $order = (new OrderModel())->where('customer_id', $this->customerId())->findByOrderNumber($orderNumber);
        if (! $order) {
            throw PageNotFoundException::forPageNotFound();
        }

        $items = (new OrderItemModel())->forOrder((int) $order['id']);

        return $this->page('account/order-detail', 'Order ' . $order['order_number'], [
            'order'     => $order,
            'items'     => $items,
            'activeNav' => 'orders',
        ]);
    }

    public function addresses()
    {
        return $this->page('account/addresses', 'My Addresses', [
            'addresses' => (new CustomerAddressModel())->forCustomer($this->customerId())->findAll(),
            'activeNav' => 'addresses',
        ]);
    }

    public function addAddress()
    {
        $rules = [
            'contact_name'  => 'required|min_length[2]|max_length[160]',
            'contact_phone' => 'required|min_length[8]|max_length[20]',
            'address_line1' => 'required|max_length[255]',
            'city'          => 'required|max_length[100]',
            'state'         => 'required|max_length[100]',
            'postal_code'   => 'required|max_length[12]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/account/addresses')->withInput()->with('errors', $this->validator->getErrors());
        }

        (new CustomerAddressModel())->insert([
            'customer_id'   => $this->customerId(),
            'label'         => trim((string) $this->request->getPost('label')) ?: 'Address',
            'contact_name'  => trim((string) $this->request->getPost('contact_name')),
            'contact_phone' => trim((string) $this->request->getPost('contact_phone')),
            'address_line1' => trim((string) $this->request->getPost('address_line1')),
            'address_line2' => trim((string) $this->request->getPost('address_line2')) ?: null,
            'city'          => trim((string) $this->request->getPost('city')),
            'state'         => trim((string) $this->request->getPost('state')),
            'postal_code'   => trim((string) $this->request->getPost('postal_code')),
            'country'       => 'IN',
        ]);

        session()->setFlashdata('success', 'Address added.');

        return redirect()->to('/account/addresses');
    }

    public function updateAddress(int $id)
    {
        $model   = new CustomerAddressModel();
        $address = $model->where('customer_id', $this->customerId())->find($id);
        if (! $address) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'contact_name'  => 'required|min_length[2]|max_length[160]',
            'contact_phone' => 'required|min_length[8]|max_length[20]',
            'address_line1' => 'required|max_length[255]',
            'city'          => 'required|max_length[100]',
            'state'         => 'required|max_length[100]',
            'postal_code'   => 'required|max_length[12]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/account/addresses')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, [
            'label'         => trim((string) $this->request->getPost('label')) ?: 'Address',
            'contact_name'  => trim((string) $this->request->getPost('contact_name')),
            'contact_phone' => trim((string) $this->request->getPost('contact_phone')),
            'address_line1' => trim((string) $this->request->getPost('address_line1')),
            'address_line2' => trim((string) $this->request->getPost('address_line2')) ?: null,
            'city'          => trim((string) $this->request->getPost('city')),
            'state'         => trim((string) $this->request->getPost('state')),
            'postal_code'   => trim((string) $this->request->getPost('postal_code')),
        ]);

        session()->setFlashdata('success', 'Address updated.');

        return redirect()->to('/account/addresses');
    }

    public function deleteAddress(int $id)
    {
        $model   = new CustomerAddressModel();
        $address = $model->where('customer_id', $this->customerId())->find($id);
        if (! $address) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->delete($id);
        session()->setFlashdata('success', 'Address removed.');

        return redirect()->to('/account/addresses');
    }

    public function profile()
    {
        return $this->page('account/profile', 'My Profile', [
            'customer'  => $this->currentCustomer(),
            'activeNav' => 'profile',
        ]);
    }

    public function updateProfile()
    {
        $customerId = $this->customerId();

        $rules = [
            'name'  => 'required|min_length[2]|max_length[160]',
            'phone' => 'permit_empty|max_length[20]|is_unique[customers.phone,id,' . $customerId . ']',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/account/profile')->withInput()->with('errors', $this->validator->getErrors());
        }

        $phone = trim((string) $this->request->getPost('phone'));

        (new CustomerModel())->update($customerId, [
            'name'  => trim((string) $this->request->getPost('name')),
            'phone' => $phone !== '' ? $phone : null,
        ]);

        session()->set('customer_name', trim((string) $this->request->getPost('name')));
        session()->setFlashdata('success', 'Profile updated.');

        return redirect()->to('/account/profile');
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'      => 'required|min_length[8]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/account/profile')->withInput()->with('errors', $this->validator->getErrors());
        }

        $customers = new CustomerModel();
        $customer  = $customers->find($this->customerId());

        if (empty($customer['password_hash']) || ! password_verify((string) $this->request->getPost('current_password'), $customer['password_hash'])) {
            return redirect()->to('/account/profile')->with('errors', ['Current password is incorrect.']);
        }

        $customers->update($customer['id'], [
            'password_hash' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT),
        ]);

        session()->setFlashdata('success', 'Password updated.');

        return redirect()->to('/account/profile');
    }

    private function customerId(): int
    {
        return (int) session()->get('customer_id');
    }
}
