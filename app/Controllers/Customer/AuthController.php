<?php

namespace App\Controllers\Customer;

use App\Libraries\Notifier;
use App\Libraries\SmsProvider;
use App\Models\CartItemModel;
use App\Models\CustomerModel;
use App\Models\CustomerTokenModel;
use App\Models\OtpCodeModel;
use App\Traits\RendersStorefrontPages;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    use RendersStorefrontPages;

    private const EMAIL_VERIFY_TTL = 86400; // 24h
    private const PASSWORD_RESET_TTL = 3600; // 1h

    public function showRegister()
    {
        if (session()->get('is_customer_authenticated') === true) {
            return redirect()->to('/account');
        }

        return $this->page('auth/register', 'Create Account', ['active' => 'shop']);
    }

    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[160]',
            'email'    => 'required|valid_email|max_length[190]|is_unique[customers.email]',
            'password' => 'required|min_length[8]',
            'phone'    => 'permit_empty|max_length[20]|is_unique[customers.phone]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/register')->withInput()->with('errors', $this->validator->getErrors());
        }

        $customers = new CustomerModel();
        $phone     = trim((string) $this->request->getPost('phone'));

        $customerId = $customers->insert([
            'name'          => trim((string) $this->request->getPost('name')),
            'email'         => trim((string) $this->request->getPost('email')),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'phone'         => $phone !== '' ? $phone : null,
        ], true);

        $this->sendVerificationEmail((int) $customerId, (string) $this->request->getPost('email'));

        session()->setFlashdata('success', 'Account created. Please check your email to verify your address before signing in.');

        return redirect()->to('/login');
    }

    public function showLogin()
    {
        if (session()->get('is_customer_authenticated') === true) {
            return redirect()->to('/account');
        }

        return $this->page('auth/login', 'Sign In', ['active' => 'shop']);
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/login')->withInput()->with('errors', $this->validator->getErrors());
        }

        $customers = new CustomerModel();
        $customer  = $customers->findByEmail((string) $this->request->getPost('email'));

        if (! $customer || empty($customer['password_hash']) || ! password_verify((string) $this->request->getPost('password'), $customer['password_hash'])) {
            return redirect()->to('/login')->withInput()->with('errors', ['Invalid email or password.']);
        }

        if ((int) $customer['is_active'] !== 1) {
            return redirect()->to('/login')->with('errors', ['This account has been deactivated. Contact support for help.']);
        }

        $this->establishSession($customer);

        return redirect()->to($this->consumePostLoginRedirect());
    }

    public function logout()
    {
        session()->remove(['is_customer_authenticated', 'customer_id', 'customer_name', 'customer_email']);

        return redirect()->to('/')->with('message', 'You have been signed out.');
    }

    public function verifyEmail(string $token)
    {
        $row = (new CustomerTokenModel())->consume($token, 'email_verify');

        if (! $row) {
            return redirect()->to('/login')->with('errors', ['This verification link is invalid or has expired.']);
        }

        (new CustomerModel())->update($row['customer_id'], ['email_verified_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/login')->with('success', 'Email verified. You can now sign in.');
    }

    public function showForgotPassword()
    {
        return $this->page('auth/forgot-password', 'Forgot Password', ['active' => 'shop']);
    }

    public function forgotPassword()
    {
        $rules = ['email' => 'required|valid_email'];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->to('/forgot-password')->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = (string) $this->request->getPost('email');
        $customer = (new CustomerModel())->findByEmail($email);

        if ($customer) {
            $raw = (new CustomerTokenModel())->issue((int) $customer['id'], 'password_reset', self::PASSWORD_RESET_TTL);
            $link = site_url('reset-password/' . $raw);
            Notifier::sendCustomerEmail(
                $email,
                'Reset your BSAS password',
                "Hello {$customer['name']},\n\nReset your password using the link below (valid for 1 hour):\n{$link}\n\nIf you did not request this, you can ignore this email."
            );
        }

        // Always show the same message, whether or not the email exists, to avoid leaking account existence.
        session()->setFlashdata('success', 'If an account exists for that email, a password reset link has been sent.');

        return redirect()->to('/login');
    }

    public function showResetPassword(string $token)
    {
        return $this->page('auth/reset-password', 'Reset Password', ['active' => 'shop', 'token' => $token]);
    }

    public function resetPassword()
    {
        $rules = [
            'token'    => 'required',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = (string) $this->request->getPost('token');
        $row   = (new CustomerTokenModel())->consume($token, 'password_reset');

        if (! $row) {
            return redirect()->to('/forgot-password')->with('errors', ['This reset link is invalid or has expired.']);
        }

        (new CustomerModel())->update($row['customer_id'], [
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        session()->setFlashdata('success', 'Password updated. Please sign in.');

        return redirect()->to('/login');
    }

    public function googleRedirect()
    {
        $clientId    = (string) env('oauth.google.clientId');
        $redirectUri = (string) env('oauth.google.redirectUri');

        if ($clientId === '' || $redirectUri === '') {
            return redirect()->to('/login')->with('errors', ['Google sign-in is not configured yet.']);
        }

        $state = bin2hex(random_bytes(16));
        session()->set('google_oauth_state', $state);

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]);

        return redirect()->to('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    public function googleCallback()
    {
        $state = (string) $this->request->getGet('state');
        $code  = (string) $this->request->getGet('code');

        if ($state === '' || $state !== session()->get('google_oauth_state') || $code === '') {
            return redirect()->to('/login')->with('errors', ['Google sign-in failed. Please try again.']);
        }
        session()->remove('google_oauth_state');

        try {
            $client       = \Config\Services::curlrequest();
            $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code'          => $code,
                    'client_id'     => (string) env('oauth.google.clientId'),
                    'client_secret' => (string) env('oauth.google.clientSecret'),
                    'redirect_uri'  => (string) env('oauth.google.redirectUri'),
                    'grant_type'    => 'authorization_code',
                ],
            ]);
            $tokenData = json_decode((string) $tokenResponse->getBody(), true);

            if (empty($tokenData['access_token'])) {
                throw new \RuntimeException('No access token returned by Google.');
            }

            $profileResponse = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => ['Authorization' => 'Bearer ' . $tokenData['access_token']],
            ]);
            $profile = json_decode((string) $profileResponse->getBody(), true);
        } catch (\Throwable $e) {
            log_message('error', 'Google OAuth failed: ' . $e->getMessage());

            return redirect()->to('/login')->with('errors', ['Google sign-in failed. Please try again.']);
        }

        if (empty($profile['sub']) || empty($profile['email'])) {
            return redirect()->to('/login')->with('errors', ['Google did not return a valid profile.']);
        }

        $customers = new CustomerModel();
        $customer  = $customers->findByGoogleId((string) $profile['sub']) ?? $customers->findByEmail((string) $profile['email']);

        if ($customer) {
            $customers->update($customer['id'], [
                'google_id'         => (string) $profile['sub'],
                'email_verified_at' => $customer['email_verified_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $customer = $customers->find($customer['id']);
        } else {
            $id = $customers->insert([
                'name'              => (string) ($profile['name'] ?? $profile['email']),
                'email'             => (string) $profile['email'],
                'google_id'         => (string) $profile['sub'],
                'email_verified_at' => date('Y-m-d H:i:s'),
            ], true);
            $customer = $customers->find($id);
        }

        $this->establishSession($customer);

        return redirect()->to($this->consumePostLoginRedirect());
    }

    public function requestOtp()
    {
        $rules = ['phone' => 'required|max_length[20]'];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $phone     = trim((string) $this->request->getPost('phone'));
        $throttler = \Config\Services::throttler();
        if (! $throttler->check(md5($phone . 'otp'), 5, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['success' => false, 'errors' => ['Too many attempts. Please wait a moment before trying again.']]);
        }

        $code = (new OtpCodeModel())->issue($phone, 'login');
        SmsProvider::resolve()->send($phone, 'Your BSAS login code is ' . $code . '. It expires in 10 minutes.');

        return $this->response->setJSON(['success' => true]);
    }

    public function verifyOtp()
    {
        $rules = [
            'phone' => 'required|max_length[20]',
            'code'  => 'required|exact_length[6]',
        ];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $phone = trim((string) $this->request->getPost('phone'));
        $code  = (string) $this->request->getPost('code');

        if (! (new OtpCodeModel())->verify($phone, 'login', $code)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'errors' => ['Invalid or expired code.']]);
        }

        $customers = new CustomerModel();
        $customer  = $customers->findByPhone($phone);

        if (! $customer) {
            $id = $customers->insert([
                'name'              => 'BSAS Customer',
                'email'             => 'phone-' . preg_replace('/[^0-9]/', '', $phone) . '@otp.bsasindia.com',
                'phone'             => $phone,
                'phone_verified_at' => date('Y-m-d H:i:s'),
            ], true);
            $customer = $customers->find($id);
        } elseif (empty($customer['phone_verified_at'])) {
            $customers->update($customer['id'], ['phone_verified_at' => date('Y-m-d H:i:s')]);
            $customer = $customers->find($customer['id']);
        }

        $this->establishSession($customer);

        return $this->response->setJSON(['success' => true, 'redirect' => $this->consumePostLoginRedirect()]);
    }

    private function consumePostLoginRedirect(): string
    {
        $redirect = session()->get('post_login_redirect');
        session()->remove('post_login_redirect');

        return is_string($redirect) && $redirect !== '' ? $redirect : '/account';
    }

    private function sendVerificationEmail(int $customerId, string $email): void
    {
        $raw  = (new CustomerTokenModel())->issue($customerId, 'email_verify', self::EMAIL_VERIFY_TTL);
        $link = site_url('verify-email/' . $raw);

        Notifier::sendCustomerEmail(
            $email,
            'Verify your BSAS account',
            "Welcome to BSAS.\n\nVerify your email address using the link below (valid for 24 hours):\n{$link}"
        );
    }

    private function establishSession(array $customer): void
    {
        session()->set([
            'is_customer_authenticated' => true,
            'customer_id'                => (int) $customer['id'],
            'customer_name'              => $customer['name'],
            'customer_email'             => $customer['email'],
        ]);

        (new CustomerModel())->update($customer['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        // Merge the guest session cart (if any) into this customer's persistent cart.
        $sessionCart = session()->get('cart') ?? [];
        if ($sessionCart !== []) {
            (new CartItemModel())->mergeSessionCart((int) $customer['id'], $sessionCart);
            session()->remove('cart');
        }
    }
}
