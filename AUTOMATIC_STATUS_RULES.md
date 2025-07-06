# Automatic Status Calculation Rules

## Overview
The inventory management system now automatically calculates product status based on stock levels, with the ability to manually override to "Discontinued" when needed.

## Automatic Status Rules

### Stock Level → Status Mapping
- **Stock = 0** → **"Out of Stock"**
- **Stock = 1-5** → **"Low Stock"**  
- **Stock = 6+** → **"In Stock"**

### Manual Override
- **Only "Discontinued"** can be manually set
- When "Discontinued" is selected, it overrides the automatic calculation
- All other statuses are calculated automatically based on stock

## How It Works

### Frontend Behavior
1. **Status Display**: Shows auto-calculated status as read-only text
2. **Manual Override**: Dropdown only allows "Discontinued" or "Auto-calculate"
3. **Real-time Preview**: Status updates automatically as you change stock value

### Backend Logic
```php
// Automatic status calculation
if ($newStatus === 'Discontinued') {
    $finalStatus = 'Discontinued';  // Manual override
} else {
    $finalStatus = $this->determineStatus($newStock);  // Auto-calculate
}

private function determineStatus($stock) {
    if ($stock === 0) {
        return 'Out of Stock';
    } elseif ($stock <= 5) {
        return 'Low Stock';
    } else {
        return 'In Stock';
    }
}
```

## User Experience

### When Updating Stock
1. **Enter new stock value** (e.g., 3)
2. **Status automatically shows** "Low Stock" (read-only)
3. **Option to override** to "Discontinued" if needed
4. **Save changes** - status updates in database

### Examples
- **Stock: 0** → Status: "Out of Stock" ✅
- **Stock: 3** → Status: "Low Stock" ✅  
- **Stock: 10** → Status: "In Stock" ✅
- **Stock: 50** + Manual: "Discontinued" → Status: "Discontinued" ✅

## Benefits

1. **Consistency**: Status always matches stock levels
2. **Simplicity**: No manual status management for normal operations
3. **Flexibility**: Can still mark items as discontinued regardless of stock
4. **Accuracy**: Prevents mismatched stock/status combinations

## Database Impact

- Status field is automatically updated when stock changes
- Manual "Discontinued" status is preserved until manually changed back
- All status changes are logged for audit purposes 