<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class User extends Database
{
    private $id;
    private $first_name;
    private $last_name;
    private $email;
    private $mobile_number;
    private $address;
    private $city;
    private $state;
    private $zip;
    private $country;
    private $timezone;
    private $created;
    private $last_updated;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
        return $this;
    }

    public function toObject() {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        
        $object = new stdClass();
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $object->$propertyName = $property->getValue($this);
        }
        return $object; 
    }

    public function forDisplay() {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        
        $object = new stdClass();
        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if ($propertyName != 'created' && $propertyName != 'last_updated') {
                $object->$propertyName = $property->getValue($this);
            }
        }
        return $object;    
    }

    public function getUser($userId){
        $possibleUser = $this->resultQuery("SELECT * FROM users WHERE id = ? LIMIT 1", ["i", $userId]);
        if (count($possibleUser) === 0){
            return $this;
        }
        $this->setUser($possibleUser[0]);
        return $this->forDisplay();
    }

    public function setUser($userRow)
    {
        $this->id = isset($userRow["id"]) ? $userRow["id"] : $this->id;
        $this->first_name = isset($userRow["first_name"]) ? $userRow["first_name"] : $this->first_name;
        $this->last_name = isset($userRow["last_name"]) ? $userRow["last_name"] : $this->last_name;
        $this->email = isset($userRow["email"]) ? $userRow["email"] : $this->email;
        $this->mobile_number = isset($userRow["mobile_number"]) ? $userRow["mobile_number"] : $this->mobile_number;
        $this->address = isset($userRow["address"]) ? $userRow["address"] : $this->address;
        $this->city = isset($userRow["city"]) ? $userRow["city"] : $this->city;
        $this->state = isset($userRow["state"]) ? $userRow["state"] : $this->state;
        $this->zip = isset($userRow["zip"]) ? $userRow["zip"] : $this->zip;
        $this->country = isset($userRow["country"]) ? $userRow["country"] : $this->country;
        $this->timezone = isset($userRow["timezone"]) ? $userRow["timezone"] : $this->timezone;
        $this->created = isset($userRow["created"]) ? $userRow["created"] : $this->created;
        $this->last_updated = isset($userRow["last_updated"]) ? $userRow["last_updated"] : $this->last_updated;
    }

    public function validateUser($userData){
        $errors = [];
        if (!isset($userData["first_name"])){
            $errors["first_name"] = "First Name is Required";
        }

        if (!isset($userData["last_name"])){
            $errors["last_name"] = "Last Name is Required";
        }

        if (isset($userData["email"]) && !filter_var($userData["email"], FILTER_VALIDATE_EMAIL)){
            $errors["email"] = "Invalid Email Address";
        }
        else if (!isset($userData["email"])){
            $errors["email"] = "Email Address is Required";
        }

        if (!isset($userData["address"])){
            $errors["address"] = "Address is Required";
        }

        if (!isset($userData["city"])){
            $errors["city"] = "City is Required";
        }

        if (isset($userData["state"]) && (strlen($userData["state"]) > 2 || strlen($userData["state"]) < 2 )){
            $errors["state"] = "State must be 2 characters";
        } else if (!isset($userData["state"])){
            $errors["state"] = "State is Required";
        }

        if (isset($userData["zip"]) && !is_numeric($userData["zip"])) {
            $errors["zip"] = "Zip must be a number";    
        }else if (!isset($userData["zip"])){
            $errors["zip"] = "Zip is Required";
        }        

        if (isset($userData["country"]) && (strlen($userData["country"]) > 2 || strlen($userData["country"]) < 2 )){
            $errors["country"] = "Country must be 2 characters";
        } else if (!isset($userData["country"])){
            $errors["country"] = "Country is Required";
        }

        return $errors;
    }   

    public function saveUser(){
        if (isset($this->id) && !empty($this->id)){
            return $this->updateUser();
        }else{
            return $this->insertUser();
        }
    }

    private function updateUser(){
        $updateUserQuery = "UPDATE users 
        SET first_name = ?, last_name = ?, email = ?, mobile_number = ?, address = ? , city = ?, state = ?, zip = ?, country = ?, timezone = ? 
        WHERE id = ? LIMIT 1";

        try {
            $this->noResultQuery($updateUserQuery, ["sssssssissi", [$this->first_name, $this->last_name, $this->email, $this->mobile_number, $this->address, $this->city, $this->state, $this->zip, $this->country, $this->timezone, $this->id]]);
        }catch(Exception $e){
            return false;
        }
        return true; 
    }

    private function insertUser(){
        $insertUserQuery = "INSERT INTO users 
        (`first_name`,`last_name`,`email`,`mobile_number`,`address`,`city`,`state`,`zip`,`country`,`timezone`, `created`) 
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        try {
            $this->noResultQuery($insertUserQuery, ["sssssssiss", [$this->first_name, $this->last_name, $this->email, $this->mobile_number, $this->address, $this->city, $this->state, $this->zip, $this->country, $this->timezone]]);
        }catch(Exception $e){
            return false;
        }
        return true; 
    }

    public function deleteUser(){
        $deleteUserQuery = "DELETE FROM users WHERE id = ? LIMIT 1";
        try {
            $this->noResultQuery($deleteUserQuery, ["i", [$this->id]]);
        }catch(Exception $e){
            return false;
        }
        return true;
    }
}
?>
