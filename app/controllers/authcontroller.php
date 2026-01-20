<?php
require_once __DIR__ . '/../core/controller.php';

class AuthController extends Controller {

    public function login() {
        $data = ['error' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            if (!empty($username) && !empty($password)) {
                $userModel = $this->model('User'); 
                $user = $userModel->findUserByUsername($username);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['faculty_id'] = $user['faculty_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['profile_image'] = $user['profile_image'];
                    $_SESSION['force_password_change'] = (int)$user['force_password_change'];

                    $logModel = $this->model('ActivityLog');
                    $logModel->log($user['id'], 'Login', 'User logged in successfully');

                    if ($_SESSION['force_password_change']) {
                        header('Location: change_password.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                } else {
                    $data['error'] = 'Invalid username or password.';
                }
            } else {
                $data['error'] = 'Please enter both username and password.';
            }
        }
        
        $this->view('login_view', $data);
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $logModel = $this->model('ActivityLog');
            $logModel->log($_SESSION['user_id'], 'Logout', 'User logged out');
        }
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }

    public function changePassword() {
        $this->requireLogin();
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        
        $data = [
            'error' => '', 
            'success' => '', 
            'pageTitle' => 'Change Password',
            'firstLogin' => ($_SESSION['force_password_change'] ?? 0)
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];
            
            $user = $userModel->findById($_SESSION['user_id']);

            if (!password_verify($current, $user['password'])) {
                $data['error'] = 'Current password is incorrect.';
            } elseif ($new !== $confirm) {
                $data['error'] = 'New passwords do not match.';
            } elseif (strlen($new) < 8) {
                $data['error'] = 'Password must be at least 8 characters.';
            } else {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $db = Database::getInstance();
                $db->query("UPDATE users SET password=?, force_password_change=0 WHERE id=?", [$hashed, $_SESSION['user_id']], "si");
                
                $_SESSION['force_password_change'] = 0;
                $logModel->log($_SESSION['user_id'], 'Password Changed', 'User changed password');
                
                if ($data['firstLogin']) {
                    header('Location: index.php'); exit;
                }
                $data['success'] = 'Password changed successfully!';
            }
        }
        $this->view('change_password_view', $data);
    }

    public function forgotPassword() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        $data = ['step' => 1, 'error' => '', 'success' => '', 'masked_email' => ''];
        $OTP_VALIDITY_SECONDS = 300; 

        if (isset($_SESSION['reset_step'])) {
            $data['step'] = $_SESSION['reset_step'];
            if ($data['step'] == 2 && isset($_SESSION['reset_temp_email'])) {
                $data['masked_email'] = $this->maskEmail($_SESSION['reset_temp_email']);
            }
        }

        if (isset($_SESSION['reset_time']) && (time() - $_SESSION['reset_time']) >= $OTP_VALIDITY_SECONDS) {
            $this->clearResetSession();
            $data['error'] = 'Session expired. Please start over.';
            $data['step'] = 1;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['verify_id'])) {
                $facultyId = trim($_POST['faculty_id']);
                $db = Database::getInstance();
                $res = $db->query("SELECT * FROM users WHERE faculty_id = ? AND status = 'active'", [$facultyId], "s");
                $user = $res->get_result()->fetch_assoc();

                if ($user) {
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_temp_email'] = $user['email']; 
                    $_SESSION['reset_step'] = 2;
                    $data['masked_email'] = $this->maskEmail($user['email']);
                    $data['step'] = 2;
                    $data['success'] = "Faculty ID verified.";
                } else {
                    $data['error'] = "Faculty ID not found.";
                }
            }

            if (isset($_POST['confirm_email'])) {
                $inputEmail = trim($_POST['email']);
                $realEmail = $_SESSION['reset_temp_email'] ?? '';

                if (strtolower($inputEmail) === strtolower($realEmail)) {
                    $otp = strtoupper(substr(md5(time() . rand()), 0, 6));
                    $body = "OTP for Password Reset: <h2>$otp</h2>";
        
                    if (sendEmail($realEmail, 'Password Reset OTP', $body)) {
                        $_SESSION['reset_otp'] = $otp;
                        $_SESSION['reset_time'] = time();
                        $_SESSION['reset_step'] = 3;
                        $data['step'] = 3;
                        $data['success'] = "Email confirmed! OTP sent.";
                    } else {
                        $data['error'] = "Failed to send email.";
                    }
                } else {
                    $data['error'] = "Email does not match.";
                    $data['masked_email'] = $this->maskEmail($realEmail);
                    $data['step'] = 2;
                }
            }

            if (isset($_POST['verify_otp'])) {
                $otp = strtoupper(trim($_POST['otp']));
                if ($otp === ($_SESSION['reset_otp'] ?? '')) {
                    $_SESSION['reset_otp_verified'] = true;
                    $_SESSION['reset_step'] = 4;
                    $data['step'] = 4;
                } else {
                    $data['error'] = "Invalid OTP.";
                    $data['step'] = 3;
                }
            }

            if (isset($_POST['reset_password'])) {
                $new = $_POST['new_password'];
                $confirm = $_POST['confirm_password'];

                if ($new !== $confirm) {
                    $data['error'] = "Passwords do not match.";
                    $data['step'] = 4;
                } elseif (strlen($new) < 8) {
                    $data['error'] = "Too short.";
                    $data['step'] = 4;
                } else {
                    $hashed = password_hash($new, PASSWORD_DEFAULT);
                    $db = Database::getInstance();
                    $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashed, $_SESSION['reset_user_id']], "si");
                    $this->clearResetSession();
                    $data['step'] = 5; 
                }
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'backtologin') {
             $this->clearResetSession();
             header('Location: login.php'); exit;
        }

        $this->view('forgot_password_view', $data);
    }

    private function clearResetSession() {
        unset($_SESSION['reset_otp'], $_SESSION['reset_user_id'], $_SESSION['reset_time'], $_SESSION['reset_otp_verified'], $_SESSION['reset_temp_email'], $_SESSION['reset_step']);
    }

    private function maskEmail($email) {
        $parts = explode("@", $email);
        if (count($parts) < 2) return $email;
        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);
        if ($len <= 2) return $email;
        return substr($name, 0, 1) . str_repeat("*", max($len - 2, 5)) . substr($name, -1) . "@" . $domain;
    }
}
?>