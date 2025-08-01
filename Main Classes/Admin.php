<?php
include_once 'Main Classes/User.php';

class Admin extends User{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserTypeCount()
{
    try {
        $query = "SELECT user_type, COUNT(*) as count 
                  FROM user 
                  WHERE user_type != 'admin'
                  GROUP BY user_type";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

public function getTopPerformingServices()
{
    try {
        $query = "SELECT s.service_name, COUNT(*) * 500 as income 
                  FROM booking b
                  JOIN service s ON b.service_category_id = s.service_category_id
                  WHERE b.booking_status != 'Declined-provider'
                  GROUP BY s.service_name 
                  ORDER BY income DESC 
                  LIMIT 5"; 
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

public function disableUser($user_id, $disable_status) {
    return parent::disableUser($user_id, $disable_status);
}

public function getDetails($user_id) {
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}