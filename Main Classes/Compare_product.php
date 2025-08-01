<?php
require_once __DIR__ . '/Product.php';

class Compare_product extends Product
{
    private $pdo;

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
     * Fetch all CPUs with general and CPU-specific details
     * @return array
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
     * Fetch a single CPU by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all GPUs with general and GPU-specific details
     * @return array
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
     * Fetch a single GPU by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all Motherboards with general and motherboard-specific details
     * @return array
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
     * Fetch a single Motherboard by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all Memory/RAM with general and memory-specific details
     * @return array
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
     * Fetch a single Memory/RAM by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all Storage devices with general and storage-specific details
     * @return array
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
     * Fetch a single Storage device by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all Power Supplies with general and PSU-specific details
     * @return array
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
     * Fetch a single Power Supply by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all PC Cases with general and case-specific details
     * @return array
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
     * Fetch a single PC Case by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all CPU Coolers with general and cooler-specific details
     * @return array
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
     * Fetch a single CPU Cooler by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all Monitors with general and monitor-specific details
     * @return array
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
     * Fetch a single Monitor by product_id
     * @param int $productId
     * @return array|null
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
     * Fetch all Operating Systems with general and OS-specific details
     * @return array
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
     * Fetch a single Operating System by product_id
     * @param int $productId
     * @return array|null
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
