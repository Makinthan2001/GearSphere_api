<?php

/**
 * Product Comparison Management Class
 * 
 * This class extends the Product class to provide specialized functionality
 * for comparing different types of computer hardware components. It fetches
 * detailed specifications for various product categories to enable
 * side-by-side comparisons in the GearSphere system.
 * 
 * Features:
 * - Detailed product retrieval with category-specific specifications
 * - Support for all major PC component categories
 * - Individual product fetching by ID with full details
 * - Bulk product retrieval for comparison views
 * - Database joins for comprehensive product information
 * 
 * Supported Categories:
 * - CPUs (Processors)
 * - GPUs (Video Cards)
 * - Motherboards
 * - Memory (RAM)
 * - Storage (HDD/SSD)
 * - Power Supplies
 * - PC Cases
 * - CPU Coolers
 * - Monitors
 * - Operating Systems
 *
 * @extends Product
 */

require_once __DIR__ . '/Product.php';

class Compare_product extends Product
{
    /**
     * @var PDO Database connection instance
     */
    private $pdo;

    /**
     * Compare Product Constructor
     * 
     * Initializes the Compare_product class with database connection.
     * Can accept an existing PDO connection or create a new one.
     * 
     * @param PDO|null $pdo Optional existing database connection
     */
    public function __construct($pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $db = new DBConnector();
            $this->pdo = $db->connect();
        }
        parent::__construct();
    }

    /**
     * Get All CPUs with Detailed Specifications
     * 
     * Fetches all CPU products with their general product information
     * and CPU-specific technical specifications for comparison purposes.
     * 
     * @return array Array of CPU products with detailed specifications including:
     *               - series, socket, core_count, thread_count, core_clock,
     *               - core_boost_clock, tdp, integrated_graphics
     */
    public function getAllCPUsWithDetails()
    {
        $sql = "SELECT p.*, c.series, c.socket, c.core_count, c.thread_count, c.core_clock, c.core_boost_clock, c.tdp, c.integrated_graphics
                FROM products p
                INNER JOIN cpu c ON p.product_id = c.product_id
                WHERE p.category = 'CPU'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single CPU by Product ID
     * 
     * Fetches a specific CPU product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the CPU product
     * @return array|null CPU product with specifications or null if not found
     */
    public function getCPUById($productId)
    {
        $sql = "SELECT p.*, c.series, c.socket, c.core_count, c.thread_count, c.core_clock, c.core_boost_clock, c.tdp, c.integrated_graphics
                FROM products p
                INNER JOIN cpu c ON p.product_id = c.product_id
                WHERE p.category = 'CPU' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All GPUs with Detailed Specifications
     * 
     * Fetches all GPU/Video Card products with their general information
     * and GPU-specific technical specifications for comparison.
     * 
     * @return array Array of GPU products with detailed specifications including:
     *               - chipset, memory, memory_type, core_clock, boost_clock,
     *               - interface, length, tdp, cooling
     */
    public function getAllGPUsWithDetails()
    {
        $sql = "SELECT p.*, v.chipset, v.memory, v.memory_type, v.core_clock, v.boost_clock, v.interface, v.length, v.tdp, v.cooling
                FROM products p
                INNER JOIN video_card v ON p.product_id = v.product_id
                WHERE p.category = 'Video Card'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single GPU by Product ID
     * 
     * Fetches a specific GPU product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the GPU product
     * @return array|null GPU product with specifications or null if not found
     */
    public function getGPUById($productId)
    {
        $sql = "SELECT p.*, v.chipset, v.memory, v.memory_type, v.core_clock, v.boost_clock, v.interface, v.length, v.tdp, v.cooling
                FROM products p
                INNER JOIN video_card v ON p.product_id = v.product_id
                WHERE p.category = 'Video Card' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Motherboards with Detailed Specifications
     * 
     * Fetches all motherboard products with their general information
     * and motherboard-specific technical specifications for comparison.
     * 
     * @return array Array of motherboard products with detailed specifications including:
     *               - socket, form_factor, chipset, memory_max, memory_slots,
     *               - memory_type, sata_ports, wifi
     */
    public function getAllMotherboardsWithDetails()
    {
        $sql = "SELECT p.*, m.socket, m.form_factor, m.chipset, m.memory_max, m.memory_slots, m.memory_type, m.sata_ports, m.wifi
                FROM products p
                INNER JOIN motherboard m ON p.product_id = m.product_id
                WHERE p.category = 'Motherboard'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single Motherboard by Product ID
     * 
     * Fetches a specific motherboard product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the motherboard product
     * @return array|null Motherboard product with specifications or null if not found
     */
    public function getMotherBoardById($productId)
    {
        $sql = "SELECT p.*, m.socket, m.form_factor, m.chipset, m.memory_max, m.memory_slots, m.memory_type, m.sata_ports, m.wifi
                FROM products p
                INNER JOIN motherboard m ON p.product_id = m.product_id
                WHERE p.category = 'Motherboard' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Memory/RAM with Detailed Specifications
     * 
     * Fetches all memory products with their general information
     * and memory-specific technical specifications for comparison.
     * 
     * @return array Array of memory products with detailed specifications including:
     *               - memory_type, speed, modules, cas_latency, voltage
     */
    public function getAllMemoryWithDetails()
    {
        $sql = "SELECT p.*, m.memory_type, m.speed, m.modules, m.cas_latency, m.voltage
                FROM products p
                INNER JOIN memory m ON p.product_id = m.product_id
                WHERE p.category = 'Memory'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single Memory/RAM by Product ID
     * 
     * Fetches a specific memory product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the memory product
     * @return array|null Memory product with specifications or null if not found
     */
    public function getMemoryById($productId)
    {
        $sql = "SELECT p.*, m.memory_type, m.speed, m.modules, m.cas_latency, m.voltage
                FROM products p
                INNER JOIN memory m ON p.product_id = m.product_id
                WHERE p.category = 'Memory' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Storage Devices with Detailed Specifications
     * 
     * Fetches all storage products with their general information
     * and storage-specific technical specifications for comparison.
     * 
     * @return array Array of storage products with detailed specifications including:
     *               - storage_type, capacity, interface, form_factor
     */
    public function getAllStorageWithDetails()
    {
        $sql = "SELECT p.*, s.storage_type, s.capacity, s.interface, s.form_factor
                FROM products p
                INNER JOIN storage s ON p.product_id = s.product_id
                WHERE p.category = 'Storage'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single Storage Device by Product ID
     * 
     * Fetches a specific storage product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the storage product
     * @return array|null Storage product with specifications or null if not found
     */
    public function getStorageById($productId)
    {
        $sql = "SELECT p.*, s.storage_type, s.capacity, s.interface, s.form_factor
                FROM products p
                INNER JOIN storage s ON p.product_id = s.product_id
                WHERE p.category = 'Storage' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Power Supplies with Detailed Specifications
     * 
     * Fetches all power supply products with their general information
     * and PSU-specific technical specifications for comparison.
     * 
     * @return array Array of power supply products with detailed specifications including:
     *               - wattage, psu_type, efficiency_rating, length, modular, sata_connectors
     */
    public function getAllPowerSuppliesWithDetails()
    {
        $sql = "SELECT p.*, ps.wattage, ps.type AS psu_type, ps.efficiency_rating, ps.length, ps.modular, ps.sata_connectors
                FROM products p
                INNER JOIN power_supply ps ON p.product_id = ps.product_id
                WHERE p.category = 'Power Supply'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single Power Supply by Product ID
     * 
     * Fetches a specific power supply product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the power supply product
     * @return array|null Power supply product with specifications or null if not found
     */
    public function getPowerSupplyById($productId)
    {
        $sql = "SELECT p.*, ps.wattage, ps.type AS psu_type, ps.efficiency_rating, ps.length, ps.modular, ps.sata_connectors
                FROM products p
                INNER JOIN power_supply ps ON p.product_id = ps.product_id
                WHERE p.category = 'Power Supply' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All PC Cases with Detailed Specifications
     * 
     * Fetches all PC case products with their general information
     * and case-specific technical specifications for comparison.
     * 
     * @return array Array of PC case products with detailed specifications including:
     *               - type, side_panel, color, max_gpu_length, volume, dimensions
     */
    public function getAllPCCasesWithDetails()
    {
        $sql = "SELECT p.*, pc.type, pc.side_panel, pc.color, pc.max_gpu_length, pc.volume, pc.dimensions
                FROM products p
                INNER JOIN pc_case pc ON p.product_id = pc.product_id
                WHERE p.category = 'PC Case'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single PC Case by Product ID
     * 
     * Fetches a specific PC case product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the PC case product
     * @return array|null PC case product with specifications or null if not found
     */
    public function getPCCaseById($productId)
    {
        $sql = "SELECT p.*, pc.type, pc.side_panel, pc.color, pc.max_gpu_length, pc.volume, pc.dimensions
                FROM products p
                INNER JOIN pc_case pc ON p.product_id = pc.product_id
                WHERE p.category = 'PC Case' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All CPU Coolers with Detailed Specifications
     * 
     * Fetches all CPU cooler products with their general information
     * and cooler-specific technical specifications for comparison.
     * 
     * @return array Array of CPU cooler products with detailed specifications including:
     *               - fan_rpm, noise_level, color, height, water_cooled
     */
    public function getAllCPUCoolersWithDetails()
    {
        $sql = "SELECT p.*, cc.fan_rpm, cc.noise_level, cc.color, cc.height, cc.water_cooled
                FROM products p
                INNER JOIN cpu_cooler cc ON p.product_id = cc.product_id
                WHERE p.category = 'CPU Cooler'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single CPU Cooler by Product ID
     * 
     * Fetches a specific CPU cooler product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the CPU cooler product
     * @return array|null CPU cooler product with specifications or null if not found
     */
    public function getCPUCoolerById($productId)
    {
        $sql = "SELECT p.*, cc.fan_rpm, cc.noise_level, cc.color, cc.height, cc.water_cooled
                FROM products p
                INNER JOIN cpu_cooler cc ON p.product_id = cc.product_id
                WHERE p.category = 'CPU Cooler' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Monitors with Detailed Specifications
     * 
     * Fetches all monitor products with their general information
     * and monitor-specific technical specifications for comparison.
     * 
     * @return array Array of monitor products with detailed specifications including:
     *               - screen_size, resolution, refresh_rate, panel_type, aspect_ratio, brightness
     */
    public function getAllMonitorsWithDetails()
    {
        $sql = "SELECT p.*, m.screen_size, m.resolution, m.refresh_rate, m.panel_type, m.aspect_ratio, m.brightness
                FROM products p
                INNER JOIN monitor m ON p.product_id = m.product_id
                WHERE p.category = 'Monitor'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single Monitor by Product ID
     * 
     * Fetches a specific monitor product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the monitor product
     * @return array|null Monitor product with specifications or null if not found
     */
    public function getMonitorById($productId)
    {
        $sql = "SELECT p.*, m.screen_size, m.resolution, m.refresh_rate, m.panel_type, m.aspect_ratio, m.brightness
                FROM products p
                INNER JOIN monitor m ON p.product_id = m.product_id
                WHERE p.category = 'Monitor' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get All Operating Systems with Detailed Specifications
     * 
     * Fetches all operating system products with their general information
     * and OS-specific technical specifications for comparison.
     * 
     * @return array Array of operating system products with detailed specifications including:
     *               - model, mode, version, max_supported_memory
     */
    public function getAllOperatingSystemsWithDetails()
    {
        $sql = "SELECT p.*, os.model, os.mode, os.version, os.max_supported_memory
                FROM products p
                INNER JOIN operating_system os ON p.product_id = os.product_id
                WHERE p.category = 'Operating System'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Single Operating System by Product ID
     * 
     * Fetches a specific operating system product with complete specifications
     * for detailed comparison or individual product display.
     * 
     * @param int $productId The unique identifier of the operating system product
     * @return array|null Operating system product with specifications or null if not found
     */
    public function getOperatingSystemById($productId)
    {
        $sql = "SELECT p.*, os.model, os.mode, os.version, os.max_supported_memory
                FROM products p
                INNER JOIN operating_system os ON p.product_id = os.product_id
                WHERE p.category = 'Operating System' AND p.product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
