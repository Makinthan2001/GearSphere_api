# GearSphere Product Management API

This API provides endpoints for managing products in the GearSphere system.

## Base URL
```
http://localhost/gearsphere_api/GearSphere-Backend/
```

## Endpoints

### 1. Add Product
**POST** `/addProduct.php`

Add a new product with image upload.

**Form Data:**
- `name` (required): Product name
- `category` (required): Product category
- `price` (required): Product price
- `manufacturer` (required): Product manufacturer
- `description` (optional): Product description
- `type` (required): Product type for specific details
- `image_file` (optional): Product image file
- Additional fields based on product type

**Example Response:**
```json
{
    "success": true,
    "message": "Product added successfully",
    "product_id": 123
}
```

### 2. Get All Products
**GET** `/getProducts.php`

Retrieve all products with their specific details.

**Query Parameters:**
- `category` (optional): Filter by category
- `id` (optional): Get specific product by ID

**Example Response:**
```json
{
    "success": true,
    "products": [
        {
            "product_id": 123,
            "name": "Intel Core i9-13900K",
            "category": "CPU",
            "price": "599.99",
            "image_url": "uploads/1234567890_image.jpg",
            "description": "High-performance CPU",
            "manufacturer": "Intel",
            "specific_details": {
                "series": "Core i9",
                "socket": "LGA 1700",
                "core_count": 8,
                "thread_count": 16
            }
        }
    ]
}
```

### 3. Update Product
**PUT/POST** `/updateProduct.php?id={product_id}`

Update an existing product.

**Form Data:**
- Same as Add Product, plus:
- `product_id` (required): ID of product to update
- `image_file` (optional): New image file (replaces existing)

**Example Response:**
```json
{
    "success": true,
    "message": "Product updated successfully"
}
```

### 4. Delete Product
**DELETE/POST** `/deleteProduct.php?id={product_id}`

Delete a product and its associated data.

**Parameters:**
- `id` (required): Product ID to delete

**Example Response:**
```json
{
    "success": true,
    "message": "Product deleted successfully"
}
```

### 5. Delete Multiple Products
**POST** `/deleteProduct.php`

Delete multiple products at once.

**Form Data:**
- `product_ids[]` (required): Array of product IDs

**Example Response:**
```json
{
    "success": true,
    "message": "Deleted 3 products successfully",
    "deleted_count": 3,
    "error_count": 0
}
```

## Product Types and Specific Fields

### CPU
- `series`, `socket`, `core_count`, `thread_count`, `core_clock`, `core_boost_clock`, `tdp`, `integrated_graphics`

### CPU Cooler
- `fan_rpm`, `noise_level`, `color`, `height`, `water_cooled`

### Motherboard
- `socket`, `form_factor`, `chipset`, `memory_max`, `memory_slots`, `memory_type`, `sata_ports`, `wifi`

### Memory
- `memory_type`, `speed`, `modules`, `cas_latency`, `voltage`, `ecc`

### Storage
- `storage_type`, `capacity`, `interface`, `form_factor`

### Video Card
- `chipset`, `memory`, `memory_type`, `core_clock`, `boost_clock`, `tdp`, `length`, `width`, `height`

### Power Supply
- `wattage`, `efficiency_rating`, `modular`, `form_factor`, `color`

### Operating System
- `version`, `edition`, `license_type`, `architecture`, `language`

### Monitor
- `screen_size`, `resolution`, `refresh_rate`, `panel_type`, `response_time`, `connectivity`

### PC Case
- `form_factor`, `color`, `side_panel`, `external_bays`, `internal_bays`

## Error Responses

All endpoints return error responses in this format:

```json
{
    "success": false,
    "message": "Error description"
}
```

## Image Upload

- Images are stored in the `uploads/` directory
- File names are prefixed with timestamp to avoid conflicts
- Supported formats: JPG, PNG, GIF, etc.
- Maximum file size: Determined by PHP configuration

## Database Requirements

The API expects the following database tables:
- `products` (main product table)
- `cpu`, `cpu_cooler`, `motherboard`, `memory`, `storage`, `video_card`, `power_supply`, `operating_system`, `monitor`, `pc_case` (specific product tables)

Each specific table should have a `product_id` foreign key referencing the main products table. 