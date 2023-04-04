<?php

namespace App\Controllers;

// require_once APPPATH . 'ThirdParty/firebase/php-jwt/src/JWT.php';
use CodeIgniter\Controller;
use App\Models\UserModel;

use Firebase\JWT\JWT;

// use Config\Services;

class AuthController extends Controller
{

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
      $to = 'tadashiishikawa25@gmail.com'; //Type here the mail address where you want to send
      $subject = 'Subject of Email'; //Write here Subject of Email
      $message = 'Conngrats ! You did it.'; //Write the message you want to send
      $email = \Config\Services::email();
      $email->setTo($to);
      $email->setFrom('newuser@auziplan.com', 'Mail Testing'); //set From
      $email->setSubject($subject);
      $email->setMessage($message);
      if ($email->send()) {
        echo 'Email has been Sent.';
      } else {
        echo 'Something went wrong !';
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