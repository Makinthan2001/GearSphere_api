<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'DbConnector.php';

class ProductManager {
    private $db;
    
    public function __construct() {
        $connector = new DBConnector();
        $this->db = $connector->connect();
    }
    
    public function updateProduct($productId, $data, $imageFile = null) {
        try {
            $this->db->beginTransaction();
            
            // Handle image upload if new image is provided
            $imageUrl = null;
            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($imageFile['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
                    $imageUrl = $targetPath;
                }
            }
            
            // Update products table
            $sql = "UPDATE products SET 
                    name = :name, 
                    category = :category, 
                    price = :price, 
                    description = :description, 
                    manufacturer = :manufacturer,
                    stock = :stock";
            
            $params = [
                ':product_id' => $productId,
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':description' => $data['description'] ?? null,
                ':manufacturer' => $data['manufacturer'],
                ':stock' => $data['stock'] ?? 0
            ];
            
            // Add image_url to update if new image was uploaded
            if ($imageUrl) {
                $sql .= ", image_url = :image_url";
                $params[':image_url'] = $imageUrl;
            }
            
            $sql .= " WHERE product_id = :product_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Update specific product table
            $this->updateProductSpecific($data, $productId);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Product updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ];
        }
    }
    
    private function updateProductSpecific($data, $productId) {
        $type = $data['type'] ?? '';
        
        switch ($type) {
            case 'cpu':
                $this->updateCPU($data, $productId);
                break;
            case 'cpu_cooler':
                $this->updateCPUCooler($data, $productId);
                break;
            case 'motherboard':
                $this->updateMotherboard($data, $productId);
                break;
            case 'memory':
                $this->updateMemory($data, $productId);
                break;
            case 'storage':
                $this->updateStorage($data, $productId);
                break;
            case 'video_card':
                $this->updateVideoCard($data, $productId);
                break;
            case 'power_supply':
                $this->updatePowerSupply($data, $productId);
                break;
            case 'operating_system':
                $this->updateOperatingSystem($data, $productId);
                break;
            case 'monitor':
                $this->updateMonitor($data, $productId);
                break;
            case 'pc_case':
                $this->updatePCCase($data, $productId);
                break;
            default:
                // For general products, no specific table update needed
                break;
        }
    }
    
    private function updateCPU($data, $productId) {
        $sql = "UPDATE cpu SET 
                series = :series, 
                socket = :socket, 
                core_count = :core_count, 
                thread_count = :thread_count, 
                core_clock = :core_clock, 
                core_boost_clock = :core_boost_clock, 
                tdp = :tdp, 
                integrated_graphics = :integrated_graphics 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':series' => $data['series'] ?? null,
            ':socket' => $data['socket'] ?? null,
            ':core_count' => $data['core_count'] ?? null,
            ':thread_count' => $data['thread_count'] ?? null,
            ':core_clock' => $data['core_clock'] ?? null,
            ':core_boost_clock' => $data['core_boost_clock'] ?? null,
            ':tdp' => $data['tdp'] ?? null,
            ':integrated_graphics' => $data['integrated_graphics'] ?? false
        ]);
    }
    
    private function updateCPUCooler($data, $productId) {
        $sql = "UPDATE cpu_cooler SET 
                fan_rpm = :fan_rpm, 
                noise_level = :noise_level, 
                color = :color, 
                height = :height, 
                water_cooled = :water_cooled 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':fan_rpm' => $data['fan_rpm'] ?? null,
            ':noise_level' => $data['noise_level'] ?? null,
            ':color' => $data['color'] ?? null,
            ':height' => $data['height'] ?? null,
            ':water_cooled' => $data['water_cooled'] ?? false
        ]);
    }
    
    private function updateMotherboard($data, $productId) {
        $sql = "UPDATE motherboard SET 
                socket = :socket, 
                form_factor = :form_factor, 
                chipset = :chipset, 
                memory_max = :memory_max, 
                memory_slots = :memory_slots, 
                memory_type = :memory_type, 
                sata_ports = :sata_ports, 
                wifi = :wifi 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':socket' => $data['socket'] ?? null,
            ':form_factor' => $data['form_factor'] ?? null,
            ':chipset' => $data['chipset'] ?? null,
            ':memory_max' => $data['memory_max'] ?? null,
            ':memory_slots' => $data['memory_slots'] ?? null,
            ':memory_type' => $data['memory_type'] ?? null,
            ':sata_ports' => $data['sata_ports'] ?? null,
            ':wifi' => $data['wifi'] ?? false
        ]);
    }
    
    private function updateMemory($data, $productId) {
        $sql = "UPDATE memory SET 
                memory_type = :memory_type, 
                speed = :speed, 
                modules = :modules, 
                cas_latency = :cas_latency, 
                voltage = :voltage, 
                ecc = :ecc 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':memory_type' => $data['memory_type'] ?? null,
            ':speed' => $data['speed'] ?? null,
            ':modules' => $data['modules'] ?? null,
            ':cas_latency' => $data['cas_latency'] ?? null,
            ':voltage' => $data['voltage'] ?? null,
            ':ecc' => $data['ecc'] ?? false
        ]);
    }
    
    private function updateStorage($data, $productId) {
        $sql = "UPDATE storage SET 
                storage_type = :storage_type, 
                capacity = :capacity, 
                interface = :interface, 
                form_factor = :form_factor 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':storage_type' => $data['storage_type'] ?? null,
            ':capacity' => $data['capacity'] ?? null,
            ':interface' => $data['interface'] ?? null,
            ':form_factor' => $data['form_factor'] ?? null
        ]);
    }
    
    private function updateVideoCard($data, $productId) {
        $sql = "UPDATE video_card SET 
                chipset = :chipset, 
                memory = :memory, 
                memory_type = :memory_type, 
                core_clock = :core_clock, 
                boost_clock = :boost_clock, 
                tdp = :tdp, 
                length = :length, 
                width = :width, 
                height = :height 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':chipset' => $data['chipset'] ?? null,
            ':memory' => $data['memory'] ?? null,
            ':memory_type' => $data['memory_type'] ?? null,
            ':core_clock' => $data['core_clock'] ?? null,
            ':boost_clock' => $data['boost_clock'] ?? null,
            ':tdp' => $data['tdp'] ?? null,
            ':length' => $data['length'] ?? null,
            ':width' => $data['width'] ?? null,
            ':height' => $data['height'] ?? null
        ]);
    }
    
    private function updatePowerSupply($data, $productId) {
        $sql = "UPDATE power_supply SET 
                wattage = :wattage, 
                efficiency_rating = :efficiency_rating, 
                modular = :modular, 
                form_factor = :form_factor, 
                color = :color 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':wattage' => $data['wattage'] ?? null,
            ':efficiency_rating' => $data['efficiency_rating'] ?? null,
            ':modular' => $data['modular'] ?? null,
            ':form_factor' => $data['form_factor'] ?? null,
            ':color' => $data['color'] ?? null
        ]);
    }
    
    private function updateOperatingSystem($data, $productId) {
        $sql = "UPDATE operating_system SET 
                version = :version, 
                edition = :edition, 
                license_type = :license_type, 
                architecture = :architecture, 
                language = :language 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':version' => $data['version'] ?? null,
            ':edition' => $data['edition'] ?? null,
            ':license_type' => $data['license_type'] ?? null,
            ':architecture' => $data['architecture'] ?? null,
            ':language' => $data['language'] ?? null
        ]);
    }
    
    private function updateMonitor($data, $productId) {
        $sql = "UPDATE monitor SET 
                screen_size = :screen_size, 
                resolution = :resolution, 
                refresh_rate = :refresh_rate, 
                panel_type = :panel_type, 
                response_time = :response_time, 
                connectivity = :connectivity 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':screen_size' => $data['screen_size'] ?? null,
            ':resolution' => $data['resolution'] ?? null,
            ':refresh_rate' => $data['refresh_rate'] ?? null,
            ':panel_type' => $data['panel_type'] ?? null,
            ':response_time' => $data['response_time'] ?? null,
            ':connectivity' => $data['connectivity'] ?? null
        ]);
    }
    
    private function updatePCCase($data, $productId) {
        $sql = "UPDATE pc_case SET 
                form_factor = :form_factor, 
                color = :color, 
                side_panel = :side_panel, 
                external_bays = :external_bays, 
                internal_bays = :internal_bays 
                WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':form_factor' => $data['form_factor'] ?? null,
            ':color' => $data['color'] ?? null,
            ':side_panel' => $data['side_panel'] ?? null,
            ':external_bays' => $data['external_bays'] ?? null,
            ':internal_bays' => $data['internal_bays'] ?? null
        ]);
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productManager = new ProductManager();
    
    // Get product ID from URL or POST data
    $productId = $_GET['id'] ?? $_POST['product_id'] ?? null;
    
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    
    // Get form data
    $formData = $_POST;
    
    // Handle image upload
    $imageFile = isset($_FILES['image_file']) ? $_FILES['image_file'] : null;
    
    $result = $productManager->updateProduct($productId, $formData, $imageFile);
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 