<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    
    public function addProduct($data, $imageFile = null) {
        try {
            $this->db->beginTransaction();
            
            // Handle image upload
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
            
            // Calculate status based on stock
            $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
            if ($stock === 0) {
                $status = 'Out of Stock';
            } elseif ($stock <= 5) {
                $status = 'Low Stock';
            } else {
                $status = 'In Stock';
            }
            
            // Insert into products table
            $sql = "INSERT INTO products (name, category, price, image_url, description, manufacturer, stock, status) 
                    VALUES (:name, :category, :price, :image_url, :description, :manufacturer, :stock, :status)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':image_url' => $imageUrl,
                ':description' => $data['description'] ?? null,
                ':manufacturer' => $data['manufacturer'],
                ':stock' => $stock,
                ':status' => $status
            ]);
            
            $productId = $this->db->lastInsertId();
            
            // Insert into specific product table based on type
            $this->insertProductSpecific($data, $productId);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Product added successfully',
                'product_id' => $productId
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error adding product: ' . $e->getMessage()
            ];
        }
    }
    
    private function insertProductSpecific($data, $productId) {
        $type = $data['type'] ?? '';
        
        switch ($type) {
            case 'cpu':
                $this->insertCPU($data, $productId);
                break;
            case 'cpu_cooler':
                $this->insertCPUCooler($data, $productId);
                break;
            case 'motherboard':
                $this->insertMotherboard($data, $productId);
                break;
            case 'memory':
                $this->insertMemory($data, $productId);
                break;
            case 'storage':
                $this->insertStorage($data, $productId);
                break;
            case 'video_card':
                $this->insertVideoCard($data, $productId);
                break;
            case 'power_supply':
                $this->insertPowerSupply($data, $productId);
                break;
            case 'operating_system':
                $this->insertOperatingSystem($data, $productId);
                break;
            case 'monitor':
                $this->insertMonitor($data, $productId);
                break;
            case 'pc_case':
                $this->insertPCCase($data, $productId);
                break;
            default:
                // For general products, no specific table needed
                break;
        }
    }
    
    private function insertCPU($data, $productId) {
        $sql = "INSERT INTO cpu (product_id, series, socket, core_count, thread_count, core_clock, core_boost_clock, tdp, integrated_graphics) 
                VALUES (:product_id, :series, :socket, :core_count, :thread_count, :core_clock, :core_boost_clock, :tdp, :integrated_graphics)";
        
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
    
    private function insertCPUCooler($data, $productId) {
        $sql = "INSERT INTO cpu_cooler (product_id, fan_rpm, noise_level, color, height, water_cooled) 
                VALUES (:product_id, :fan_rpm, :noise_level, :color, :height, :water_cooled)";
        
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
    
    private function insertMotherboard($data, $productId) {
        $sql = "INSERT INTO motherboard (product_id, socket, form_factor, chipset, memory_max, memory_slots, memory_type, sata_ports, wifi) 
                VALUES (:product_id, :socket, :form_factor, :chipset, :memory_max, :memory_slots, :memory_type, :sata_ports, :wifi)";
        
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
    
    private function insertMemory($data, $productId) {
        $sql = "INSERT INTO memory (product_id, memory_type, speed, modules, cas_latency, voltage, ecc) 
                VALUES (:product_id, :memory_type, :speed, :modules, :cas_latency, :voltage, :ecc)";
        
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
    
    private function insertStorage($data, $productId) {
        $sql = "INSERT INTO storage (product_id, storage_type, capacity, interface, form_factor) 
                VALUES (:product_id, :storage_type, :capacity, :interface, :form_factor)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id' => $productId,
            ':storage_type' => $data['storage_type'] ?? null,
            ':capacity' => $data['capacity'] ?? null,
            ':interface' => $data['interface'] ?? null,
            ':form_factor' => $data['form_factor'] ?? null
        ]);
    }
    
    private function insertVideoCard($data, $productId) {
        $sql = "INSERT INTO video_card (product_id, chipset, memory, memory_type, core_clock, boost_clock, tdp, length, width, height) 
                VALUES (:product_id, :chipset, :memory, :memory_type, :core_clock, :boost_clock, :tdp, :length, :width, :height)";
        
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
    
    private function insertPowerSupply($data, $productId) {
        $sql = "INSERT INTO power_supply (product_id, wattage, efficiency_rating, modular, form_factor, color) 
                VALUES (:product_id, :wattage, :efficiency_rating, :modular, :form_factor, :color)";
        
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
    
    private function insertOperatingSystem($data, $productId) {
        $sql = "INSERT INTO operating_system (product_id, version, edition, license_type, architecture, language) 
                VALUES (:product_id, :version, :edition, :license_type, :architecture, :language)";
        
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
    
    private function insertMonitor($data, $productId) {
        $sql = "INSERT INTO monitor (product_id, screen_size, resolution, refresh_rate, panel_type, response_time, connectivity) 
                VALUES (:product_id, :screen_size, :resolution, :refresh_rate, :panel_type, :response_time, :connectivity)";
        
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
    
    private function insertPCCase($data, $productId) {
        $sql = "INSERT INTO pc_case (product_id, form_factor, color, side_panel, external_bays, internal_bays) 
                VALUES (:product_id, :form_factor, :color, :side_panel, :external_bays, :internal_bays)";
        
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productManager = new ProductManager();
    
    // Get form data
    $formData = $_POST;
    
    // Handle image upload
    $imageFile = isset($_FILES['image_file']) ? $_FILES['image_file'] : null;
    
    $result = $productManager->addProduct($formData, $imageFile);
    
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 