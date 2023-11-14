<?php
class UserService {
     public function getUser($userId) {
        $userModel = new User();
        return $userModel->getUser( $userId );        
    }

    public function createOrUpdateUser($userData){
        $userModel = new User();
        $validationErrors = $userModel->validateUser($userData);
        if (count($validationErrors) == 0){
            $userModel->setUser($userData);
            $saveUserSuccess = $userModel->saveUser();
            $saveMessage = 'Successfully created user';
            if (!$saveUserSuccess){
                return (object) array('message' => 'Problem saving record, contact support', 'success' => false); 
            }        
            if (!empty($userData['id'])){
                $saveMessage = "Successfully updated user";
            }
            return (object) array('message' => $saveMessage, 'success' => true);    
        } else {
            return $validationErrors;
        }        
    }

    public function deleteUser($userId) {
        if (!is_numeric($userId)) {
            return (object) array('message' => 'Problem deleting record, contact support', 'success' => false); 
        }
        
        $userModel = new User();
        $userModel->__set('id', $userId);
        $deleteUserSuccess = $userModel->deleteUser();
        $deleteMessage = 'Successfully deleted user';
        if (!$deleteUserSuccess){
            return (object) array('message' => 'Problem deleting record, contact support', 'success' => false); 
        }        
        return (object) array('message' => $deleteMessage, 'success' => true);    
    }
}
