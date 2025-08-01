<?php

/**
 * Product Management Class
 * 
 * This class handles all product-related operations in the GearSphere system.
 * It manages the complex product catalog with category-specific attributes,
 * inventory management, and image handling for all PC components.
 * 
 * @author GearSphere Team
 * @version 1.0
 */

require_once __DIR__ . '/../DbConnector.php';

class Product {
    private $pdo;  // Database connection object

    /**
     * Constructor - Initialize database connection
     * 
     * Establishes connection to the database for all product operations
     */
    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    /**
     * Add a new product to the catalog
     * 
     * Creates a new product with category-specific attributes and handles
     * image upload. Uses database transactions to ensure data consistency
     * across the main products table and category-specific tables.
     * 
     * @param array $data Product information including category-specific attributes
     * @param array|null $imageFile Uploaded image file data from $_FILES
     * @return array Result array with success status and message
     */
    public function addProduct($data, $imageFile = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Handle image upload if provided
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
            
            // Calculate product status based on stock levels
            $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
            if ($stock === 0) {
                $status = 'Out of Stock';
            } elseif ($stock <= 5) {
                $status = 'Low Stock';
            } else {
                $status = 'In Stock';
            }
            
            // Insert into main products table
            $sql = "INSERT INTO products (name, category, price, image_url, description, manufacturer, stock, status) 
                    VALUES (:name, :category, :price, :image_url, :description, :manufacturer, :stock, :status)";
            $stmt = $this->pdo->prepare($sql);
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
            $productId = $this->pdo->lastInsertId();

            // Insert category-specific attributes into appropriate table
            switch ($data['category']) {
                case 'Operating System':
                    // Store OS-specific attributes (model, mode, version, memory support)
                    $sql = "INSERT INTO operating_system (product_id, model, mode, version, max_supported_memory)
                            VALUES (:product_id, :model, :mode, :version, :max_supported_memory)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':model' => $data['model'] ?? null,
                        ':mode' => $data['mode'] ?? null,
                        ':version' => $data['version'] ?? null,
                        ':max_supported_memory' => $data['max_supported_memory'] ?? null
                    ]);
                    break;
                    
                case 'Power Supply':
                    // Store PSU-specific attributes (wattage, efficiency, modularity)
                    $sql = "INSERT INTO power_supply (product_id, wattage, type, efficiency_rating, length, modular, sata_connectors)
                            VALUES (:product_id, :wattage, :type, :efficiency_rating, :length, :modular, :sata_connectors)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':wattage' => $data['wattage'] ?? null,
                        ':type' => $data['type'] ?? null,
                        ':efficiency_rating' => $data['efficiency_rating'] ?? null,
                        ':length' => $data['length'] ?? null,
                        ':modular' => $data['modular'] ?? null,
                        ':sata_connectors' => $data['sata_connectors'] ?? null
                    ]);
                    break;
                    
                case 'Video Card':
                    // Store GPU-specific attributes (chipset, memory, clocks)
                    $sql = "INSERT INTO video_card (product_id, chipset, memory, memory_type, core_clock, boost_clock, interface, length, tdp, cooling)
                            VALUES (:product_id, :chipset, :memory, :memory_type, :core_clock, :boost_clock, :interface, :length, :tdp, :cooling)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':chipset' => $data['chipset'] ?? null,
                        ':memory' => $data['memory'] ?? null,
                        ':memory_type' => $data['memory_type'] ?? null,
                        ':core_clock' => $data['core_clock'] ?? null,
                        ':boost_clock' => $data['boost_clock'] ?? null,
                        ':interface' => $data['interface'] ?? null,
                        ':length' => $data['length'] ?? null,
                        ':tdp' => $data['tdp'] ?? null,
                        ':cooling' => $data['cooling'] ?? null
                    ]);
                    break;
                    
                case 'CPU':
                    // Store CPU-specific attributes (cores, threads, socket, clocks)
                    $sql = "INSERT INTO cpu (product_id, series, socket, core_count, thread_count, core_clock, core_boost_clock, tdp, integrated_graphics)
                            VALUES (:product_id, :series, :socket, :core_count, :thread_count, :core_clock, :core_boost_clock, :tdp, :integrated_graphics)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':series' => $data['series'] ?? null,
                        ':socket' => $data['socket'] ?? null,
                        ':core_count' => $data['core_count'] ?? null,
                        ':thread_count' => $data['thread_count'] ?? null,
                        ':core_clock' => $data['core_clock'] ?? null,
                        ':core_boost_clock' => $data['core_boost_clock'] ?? null,
                        ':tdp' => $data['tdp'] ?? null,
                        ':integrated_graphics' => $data['integrated_graphics'] ?? null
                    ]);
                    break;
                    
                case 'CPU Cooler':
                    // Store cooler-specific attributes (fan RPM, noise, cooling type)
                    $sql = "INSERT INTO cpu_cooler (product_id, fan_rpm, noise_level, color, height, water_cooled)
                            VALUES (:product_id, :fan_rpm, :noise_level, :color, :height, :water_cooled)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':fan_rpm' => $data['fan_rpm'] ?? null,
                        ':noise_level' => $data['noise_level'] ?? null,
                        ':color' => $data['color'] ?? null,
                        ':height' => $data['height'] ?? null,
                        ':water_cooled' => $data['water_cooled'] ?? null
                    ]);
                    break;
                    
                case 'Motherboard':
                    // Store motherboard-specific attributes (socket, form factor, chipset)
                    $sql = "INSERT INTO motherboard (product_id, socket, form_factor, chipset, memory_max, memory_slots, memory_type, sata_ports, wifi)
                            VALUES (:product_id, :socket, :form_factor, :chipset, :memory_max, :memory_slots, :memory_type, :sata_ports, :wifi)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':socket' => $data['socket'] ?? null,
                        ':form_factor' => $data['form_factor'] ?? null,
                        ':chipset' => $data['chipset'] ?? null,
                        ':memory_max' => $data['memory_max'] ?? null,
                        ':memory_slots' => $data['memory_slots'] ?? null,
                        ':memory_type' => $data['memory_type'] ?? null,
                        ':sata_ports' => $data['sata_ports'] ?? null,
                        ':wifi' => $data['wifi'] ?? null
                    ]);
                    break;
                    
                case 'Memory':
                    // Store RAM-specific attributes (type, speed, timings)
                    $sql = "INSERT INTO memory (product_id, memory_type, speed, modules, cas_latency, voltage)
                            VALUES (:product_id, :memory_type, :speed, :modules, :cas_latency, :voltage)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':memory_type' => $data['memory_type'] ?? null,
                        ':speed' => $data['speed'] ?? null,
                        ':modules' => $data['modules'] ?? null,
                        ':cas_latency' => $data['cas_latency'] ?? null,
                        ':voltage' => $data['voltage'] ?? null
                    ]);
                    break;
                    
                case 'Storage':
                    // Store storage-specific attributes (type, capacity, interface)
                    $sql = "INSERT INTO storage (product_id, storage_type, capacity, interface, form_factor)
                            VALUES (:product_id, :storage_type, :capacity, :interface, :form_factor)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':storage_type' => $data['storage_type'] ?? null,
                        ':capacity' => $data['capacity'] ?? null,
                        ':interface' => $data['interface'] ?? null,
                        ':form_factor' => $data['form_factor'] ?? null
                    ]);
                    break;
                    
                case 'Monitor':
                    // Store monitor-specific attributes (size, resolution, refresh rate)
                    $sql = "INSERT INTO monitor (product_id, screen_size, resolution, refresh_rate, panel_type, aspect_ratio, brightness)
                            VALUES (:product_id, :screen_size, :resolution, :refresh_rate, :panel_type, :aspect_ratio, :brightness)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':screen_size' => $data['screen_size'] ?? null,
                        ':resolution' => $data['resolution'] ?? null,
                        ':refresh_rate' => $data['refresh_rate'] ?? null,
                        ':panel_type' => $data['panel_type'] ?? null,
                        ':aspect_ratio' => $data['aspect_ratio'] ?? null,
                        ':brightness' => $data['brightness'] ?? null
                    ]);
                    break;
                    
                case 'PC Case':
                    // Store case-specific attributes (type, color, dimensions)
                    $sql = "INSERT INTO pc_case (product_id, type, side_panel, color, max_gpu_length, volume, dimensions)
                            VALUES (:product_id, :type, :side_panel, :color, :max_gpu_length, :volume, :dimensions)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':product_id' => $productId,
                        ':type' => $data['type'] ?? null,
                        ':side_panel' => $data['side_panel'] ?? null,
                        ':color' => $data['color'] ?? null,
                        ':max_gpu_length' => $data['max_gpu_length'] ?? null,
                        ':volume' => $data['volume'] ?? null,
                        ':dimensions' => $data['dimensions'] ?? null
                    ]);
                    break;
            }
            
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Product added successfully',
                'product_id' => $productId
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error adding product: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update an existing product's information
     * 
     * Updates both the main product data and category-specific attributes.
     * Handles optional image replacement and recalculates stock status.
     * 
     * @param int $productId ID of the product to update
     * @param array $data Updated product information
     * @param array|null $imageFile New image file if being replaced
     * @return array Result array with success status and message
     */
    public function updateProduct($productId, $data, $imageFile = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Handle new image upload if provided
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
            
            // Recalculate status based on new stock level
            $newStock = isset($data['stock']) ? (int)$data['stock'] : 0;
            if ($newStock === 0) {
                $newStatus = 'Out of Stock';
            } elseif ($newStock <= 5) {
                $newStatus = 'Low Stock';
            } else {
                $newStatus = 'In Stock';
            }
            
            // Update main products table
            $sql = "UPDATE products SET 
                    name = :name, 
                    category = :category, 
                    price = :price, 
                    description = :description, 
                    manufacturer = :manufacturer,
                    stock = :stock,
                    status = :status";
            $params = [
                ':product_id' => $productId,
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':description' => $data['description'] ?? null,
                ':manufacturer' => $data['manufacturer'],
                ':stock' => $data['stock'] ?? 0,
                ':status' => $newStatus
            ];
            
            // Add image URL to update if new image provided
            if ($imageUrl) {
                $sql .= ", image_url = :image_url";
                $params[':image_url'] = $imageUrl;
            }
            $sql .= " WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // Update category-specific table based on product category
            switch ($data['category']) {
                case 'Operating System':
                    $sql = "UPDATE operating_system SET model = :model, mode = :mode, version = :version, max_supported_memory = :max_supported_memory WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':model' => $data['model'] ?? null,
                        ':mode' => $data['mode'] ?? null,
                        ':version' => $data['version'] ?? null,
                        ':max_supported_memory' => $data['max_supported_memory'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                    
                case 'Power Supply':
                    $sql = "UPDATE power_supply SET wattage = :wattage, type = :type, efficiency_rating = :efficiency_rating, length = :length, modular = :modular, sata_connectors = :sata_connectors WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':wattage' => $data['wattage'] ?? null,
                        ':type' => $data['type'] ?? null,
                        ':efficiency_rating' => $data['efficiency_rating'] ?? null,
                        ':length' => $data['length'] ?? null,
                        ':modular' => $data['modular'] ?? null,
                        ':sata_connectors' => $data['sata_connectors'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'Video Card':
                    $sql = "UPDATE video_card SET chipset = :chipset, memory = :memory, memory_type = :memory_type, core_clock = :core_clock, boost_clock = :boost_clock, interface = :interface, length = :length, tdp = :tdp, cooling = :cooling WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':chipset' => $data['chipset'] ?? null,
                        ':memory' => $data['memory'] ?? null,
                        ':memory_type' => $data['memory_type'] ?? null,
                        ':core_clock' => $data['core_clock'] ?? null,
                        ':boost_clock' => $data['boost_clock'] ?? null,
                        ':interface' => $data['interface'] ?? null,
                        ':length' => $data['length'] ?? null,
                        ':tdp' => $data['tdp'] ?? null,
                        ':cooling' => $data['cooling'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'CPU':
                    $sql = "UPDATE cpu SET series = :series, socket = :socket, core_count = :core_count, thread_count = :thread_count, core_clock = :core_clock, core_boost_clock = :core_boost_clock, tdp = :tdp, integrated_graphics = :integrated_graphics WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':series' => $data['series'] ?? null,
                        ':socket' => $data['socket'] ?? null,
                        ':core_count' => $data['core_count'] ?? null,
                        ':thread_count' => $data['thread_count'] ?? null,
                        ':core_clock' => $data['core_clock'] ?? null,
                        ':core_boost_clock' => $data['core_boost_clock'] ?? null,
                        ':tdp' => $data['tdp'] ?? null,
                        ':integrated_graphics' => $data['integrated_graphics'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'CPU Cooler':
                    $sql = "UPDATE cpu_cooler SET fan_rpm = :fan_rpm, noise_level = :noise_level, color = :color, height = :height, water_cooled = :water_cooled WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':fan_rpm' => $data['fan_rpm'] ?? null,
                        ':noise_level' => $data['noise_level'] ?? null,
                        ':color' => $data['color'] ?? null,
                        ':height' => $data['height'] ?? null,
                        ':water_cooled' => $data['water_cooled'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'Motherboard':
                    $sql = "UPDATE motherboard SET socket = :socket, form_factor = :form_factor, chipset = :chipset, memory_max = :memory_max, memory_slots = :memory_slots, memory_type = :memory_type, sata_ports = :sata_ports, wifi = :wifi WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':socket' => $data['socket'] ?? null,
                        ':form_factor' => $data['form_factor'] ?? null,
                        ':chipset' => $data['chipset'] ?? null,
                        ':memory_max' => $data['memory_max'] ?? null,
                        ':memory_slots' => $data['memory_slots'] ?? null,
                        ':memory_type' => $data['memory_type'] ?? null,
                        ':sata_ports' => $data['sata_ports'] ?? null,
                        ':wifi' => $data['wifi'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'Memory':
                    $sql = "UPDATE memory SET memory_type = :memory_type, speed = :speed, modules = :modules, cas_latency = :cas_latency, voltage = :voltage WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':memory_type' => $data['memory_type'] ?? null,
                        ':speed' => $data['speed'] ?? null,
                        ':modules' => $data['modules'] ?? null,
                        ':cas_latency' => $data['cas_latency'] ?? null,
                        ':voltage' => $data['voltage'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'Storage':
                    $sql = "UPDATE storage SET storage_type = :storage_type, capacity = :capacity, interface = :interface, form_factor = :form_factor WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':storage_type' => $data['storage_type'] ?? null,
                        ':capacity' => $data['capacity'] ?? null,
                        ':interface' => $data['interface'] ?? null,
                        ':form_factor' => $data['form_factor'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'Monitor':
                    $sql = "UPDATE monitor SET screen_size = :screen_size, resolution = :resolution, refresh_rate = :refresh_rate, panel_type = :panel_type, aspect_ratio = :aspect_ratio, brightness = :brightness WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':screen_size' => $data['screen_size'] ?? null,
                        ':resolution' => $data['resolution'] ?? null,
                        ':refresh_rate' => $data['refresh_rate'] ?? null,
                        ':panel_type' => $data['panel_type'] ?? null,
                        ':aspect_ratio' => $data['aspect_ratio'] ?? null,
                        ':brightness' => $data['brightness'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
                case 'PC Case':
                    $sql = "UPDATE pc_case SET type = :type, side_panel = :side_panel, color = :color, max_gpu_length = :max_gpu_length, volume = :volume, dimensions = :dimensions WHERE product_id = :product_id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        ':type' => $data['type'] ?? null,
                        ':side_panel' => $data['side_panel'] ?? null,
                        ':color' => $data['color'] ?? null,
                        ':max_gpu_length' => $data['max_gpu_length'] ?? null,
                        ':volume' => $data['volume'] ?? null,
                        ':dimensions' => $data['dimensions'] ?? null,
                        ':product_id' => $productId
                    ]);
                    break;
            }
            
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Product updated successfully'
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update product stock levels and status
     * 
     * Updates inventory levels and automatically calculates status based on
     * stock quantity. Supports manual override for discontinued products.
     * 
     * @param int $productId ID of the product to update
     * @param int $newStock New stock quantity
     * @param string|null $newStatus Manual status override (for discontinued products)
     * @param string|null $lastRestockDate Date of last restocking (defaults to current time)
     * @return array Result array with success status and updated data
     */
    public function updateStock($productId, $newStock, $newStatus = null, $lastRestockDate = null) {
        try {
            // Only allow manual override for Discontinued status, otherwise auto-calculate
            if ($newStatus === 'Discontinued') {
                $finalStatus = 'Discontinued';
            } else {
                // Auto-calculate status based on stock levels
                if ($newStock == 0) {
                    $finalStatus = 'Out of Stock';
                } elseif ($newStock <= 5) {
                    $finalStatus = 'Low Stock';
                } else {
                    $finalStatus = 'In Stock';
                }
            }
            
            $finalDate = $lastRestockDate ? $lastRestockDate : date('Y-m-d H:i:s');
            
            $sql = "UPDATE products SET stock = :stock, status = :status, last_restock_date = :last_restock_date WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':stock' => $newStock,
                ':status' => $finalStatus,
                ':last_restock_date' => $finalDate,
                ':product_id' => $productId
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Stock, status, and last restock date updated successfully',
                    'data' => [
                        'product_id' => $productId,
                        'new_stock' => $newStock,
                        'new_status' => $finalStatus,
                        'last_restock_date' => $finalDate
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update stock, status, and last restock date'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating stock, status, and last restock date: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve a specific product by ID
     * 
     * Fetches complete product information from the main products table
     * for display, editing, or API responses.
     * 
     * @param int $productId ID of the product to retrieve
     * @return array|false Product data array or false if not found
     */
    public function getProductById($productId) {
        $sql = "SELECT * FROM products WHERE product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch();
        return $product;
    }

    /**
     * Retrieve all products from the catalog
     * 
     * Fetches all products ordered by ID (newest first) for
     * admin management and catalog display.
     * 
     * @return array Array of all product data
     */
    public function getAllProducts() {
        $sql = "SELECT * FROM products ORDER BY product_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retrieve products filtered by category
     * 
     * Fetches all products in a specific category for
     * category-based browsing and filtering.
     * 
     * @param string $category Product category to filter by
     * @return array Array of products in the specified category
     */
    public function getProductsByCategory($category) {
        $sql = "SELECT * FROM products WHERE category = :category ORDER BY product_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    }

    /**
     * Delete a product from the catalog
     * 
     * Removes a product from both the main products table and its
     * category-specific table. Also deletes associated image files.
     * Uses transactions to ensure data consistency.
     * 
     * @param int $productId ID of the product to delete
     * @return array Result array with success status and message
     */
    public function deleteProduct($productId) {
        try {
            $this->pdo->beginTransaction();
            
            // Get product details for category identification and image cleanup
            $sql = "SELECT category, image_url FROM products WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }
            
            // Map categories to their respective database tables
            $tableMap = [
                'CPU' => 'cpu',
                'CPU Cooler' => 'cpu_cooler',
                'Motherboard' => 'motherboard',
                'Memory' => 'memory',
                'Storage' => 'storage',
                'Video Card' => 'video_card',
                'Power Supply' => 'power_supply',
                'Operating System' => 'operating_system',
                'Monitor' => 'monitor',
                'PC Case' => 'pc_case'
            ];
            
            // Delete from category-specific table first (foreign key constraint)
            $tableName = $tableMap[$product['category']] ?? null;
            if ($tableName) {
                $sql = "DELETE FROM $tableName WHERE product_id = :product_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':product_id' => $productId]);
            }
            
            // Delete from main products table
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':product_id' => $productId]);
            
            // Delete associated image file if it exists
            if ($product['image_url'] && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }
            
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ];
        }
    }
}