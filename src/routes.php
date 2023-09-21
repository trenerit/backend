<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Firebase\JWT\JWT;

//view all contacts
$app->get('/contacts', function(Request $request, Response $response){
    $sql = "SELECT * FROM contacts ORDER BY id DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $contacts = $stmt->fetchAll();
    return $this->response->withJson($contacts);
});

$app->get('/contact/{id}', function(Request $request, Response $response, array $arr){
    $id = (int)$arr['id'];
    $sql = "SELECT * FROM contacts WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $contact = $stmt->fetchAll();
    return $this->response->withJson($contact);
});

$app->post('/contact/add', function(Request $request, Response $response){
    $input = $request->getParsedBody();
    $sql = "INSERT INTO contacts (id, surname, firstName, phoneNumber) VALUES (NULL, :surname, :firstName, :phoneNumber)";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':surname', $input['surname'], PDO::PARAM_STR);
    $stmt->bindParam(':firstName', $input['firstName'], PDO::PARAM_STR);
    $stmt->bindParam(':phoneNumber', $input['phoneNumber'], PDO::PARAM_STR);
    $stmt->execute();
    $res = ["status" => "ok"];
    return $this->response->withJson($res);
});

$app->put('/contact/{id}', function(Request $request, Response $response, array $arr){
    $id = (int)$arr['id'];
    $input = $request->getParsedBody();
    $sql = "UPDATE contacts SET surname = :surname, firstName = :firstName, phoneNumber = :phoneNumber WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':surname', $input['surname'], PDO::PARAM_STR);
    $stmt->bindParam(':firstName', $input['firstName'], PDO::PARAM_STR);
    $stmt->bindParam(':phoneNumber', $input['phoneNumber'], PDO::PARAM_INT);
    $stmt->execute();
    $res = ["status" => "ok"];
    return $this->response->withJson($res);
});

$app->delete('/contact/{id}', function(Request $request, Response $response, array $arr){
    $id = (int)$arr['id'];
    $sql = "DELETE FROM contacts WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $res = ["status" => "ok"];
    return $this->response->withJson($res);
});

$app->get('/search/{name}', function(Request $request, Response $response, array $arr){
    $sql = "SELECT * FROM contacts WHERE surname LIKE :surname2 ORDER BY id DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':surname2', $arr['name'].'%', PDO::PARAM_STR);
    $stmt->execute();
    $contacts = $stmt->fetchAll();
    return $this->response->withJson($contacts);
});

$app->post('/login',  function(Request $request, Response $response){

    $input = $request->getParsedBody();
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':email', $input['email'], PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetchObject();
    
    if(!$user) {
        return $this->response->withJson(["error" => "brak uzytkownika"]);
    }

    if(!password_verify($input['password'], $user->password)) {
        return $this->response->withJson(["error" => "bledne haslo"]);
    }

    $settings = $this->get('settings');

    $token = JWT::encode(['id' => $user->id, 'email' => $user->email], $settings['jwt']['secret'], "HS256");

    return $this->response->withJson(['token' => $token]);

});