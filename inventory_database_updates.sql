-- Inventory Management Database Updates
-- Run these SQL commands to add essential inventory management fields to your products table

-- Add status field to products table (default 'In Stock')
ALTER TABLE products 
ADD COLUMN status ENUM('In Stock', 'Low Stock', 'Out of Stock', 'Discontinued') DEFAULT 'In Stock',
ADD COLUMN last_restock_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create stock history table for tracking stock changes
CREATE TABLE IF NOT EXISTS stock_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    old_stock INT NOT NULL,
    new_stock INT NOT NULL,
    change_amount INT NOT NULL,
    change_type ENUM('INCREASE', 'DECREASE', 'ADJUSTMENT', 'SALE', 'RESTOCK') NOT NULL,
    notes TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Create index for better performance
CREATE INDEX idx_stock_history_product ON stock_history(product_id);
CREATE INDEX idx_stock_history_timestamp ON stock_history(timestamp);

-- Update existing products with default status based on current stock
UPDATE products SET 
    status = CASE 
        WHEN stock = 0 THEN 'Out of Stock'
        WHEN stock <= 5 THEN 'Low Stock'
        ELSE 'In Stock'
    END
WHERE status IS NULL;

-- Create view for low stock products (using fixed min_stock = 5)
CREATE OR REPLACE VIEW low_stock_products AS
SELECT 
    p.product_id,
    p.name,
    p.category,
    p.stock,
    5 as min_stock, -- Fixed minimum stock
    p.status,
    p.last_restock_date
FROM products p
WHERE p.stock <= 5;

-- Create view for inventory summary
CREATE OR REPLACE VIEW inventory_summary AS
SELECT 
    COUNT(*) as total_products,
    SUM(stock) as total_stock,
    SUM(stock * price) as total_value,
    COUNT(CASE WHEN stock = 0 THEN 1 END) as out_of_stock,
    COUNT(CASE WHEN stock <= 5 AND stock > 0 THEN 1 END) as low_stock
FROM products; 