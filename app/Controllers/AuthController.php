<?php

namespace App\Controllers;

// require_once APPPATH . 'ThirdParty/firebase/php-jwt/src/JWT.php';
use CodeIgniter\Controller;
use App\Models\UserModel;

use Firebase\JWT\JWT;

// use Config\Services;

class AuthController extends Controller
{

  public function __constructor() {
  }

  public function signup()
  {
    $userModel = new UserModel();

    $data = [
      'username' => $this->request->getVar('username'),
      'email' => $this->request->getVar('email'),
      'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT)
    ];

    $isExistData = $userModel->where('email', $data['email'])->first();
    if ($isExistData) {

      $status = [
        'success' => false
      ];
      $response_data = [
        'msg' => 'Email is Exist'
      ];
      $response = array_merge($status, $response_data);
      echo json_encode($response);
    } else {
      $key = 'secret';
      $iat = time(); // current timestamp value
      $exp = $iat + 3600;
      $payload = [
        'username' => $this->request->getVar('username'),
        'email' => $this->request->getVar('email'),
        'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
        'exp' => $exp
      ];
      $token = JWT::encode($payload, $key, 'HS256');

      $userData = [
        'username' => $this->request->getVar('username'),
        'email' => $this->request->getVar('email'),
        'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
        'token' => $token
      ];

      $userModel->save($userData);
      $message = "<html><body><p>Dear " . $userData['username'] . ",</p><p>Thank you for signing up with our website. Please click on the following button to verify your email address:</p>" .
           "<a href='" . base_url('verify_email/' . $userData['token']) . "' style='background-color: #4CAF50; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer;'>Verify Email Address</a>" .
           "<p>If you did not sign up for our website, please ignore this message.</p><p>Best regards,<br>Your website team</p></body></html>";

      $to = $userData['email']; //Type here the mail address where you want to send
      $subject = 'Subject of Email'; //Write here Subject of Email
      $email = \Config\Services::email();
      $email->setTo($to);
      $email->setFrom('newuser@auziplan.com', 'Mail Testing'); //set From
      $email->setSubject($subject);
      $email->setMessage($message);
      if ($email->send()) {
      } else {
      }
      
      $status = [
        'success' => true
      ];

      $response_data = [
        'data' => $userData
      ];
      $response = array_merge($status, $response_data);
      echo json_encode($response);
    }
  }

  public function login()
  {
    $session = session();

    $userModel = new UserModel();

    // $post_data = key($_POST);
    // $post_data = str_replace("_", ".", $post_data);
    // $data_object = json_decode($post_data);

    // $email = $data_object->email;
    // $password = $data_object->password;
    $email = $this->request->getVar('email');
    $password = $this->request->getVar('password');
    $data = $userModel->where('email', $email)->first();

    if ($data) {
      $pass = $data['password'];
      $authenticatePassword = password_verify($password, $pass);
      if ($authenticatePassword) {
        $status = [
          'success' => true
        ];
        $ses_data = [
          'id' => $data['id'],
          'username' => $data['username'],
          'email' => $data['email'],
          'isLoggedIn' => TRUE
        ];
        $session->set($ses_data);
        $response = array_merge($status, $ses_data);
        echo json_encode($response);

      } else {
        $session->setFlashdata('msg', 'Password is incorrect.');
        $data = [
          'success' => false,
          'msg' => 'Password is incorrect'
        ];
        header('Content-Type: application/json');
        echo json_encode($data);
      }
    } else {
      $session->setFlashdata('msg', 'Email does not exist.');
      $data = [
        'success' => false,
        'msg' => 'Email is not exist',
      ];
      echo json_encode($data);
    }
  }

}