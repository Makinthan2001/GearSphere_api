<?php
require_once __DIR__ . '/../DbConnector.php';

class Payment
{
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addPayment($order_id, $user_id, $amount, $payment_method = 'Card', $payment_status = 'pending')
    {
        try {
            $sql = "INSERT INTO payment (order_id, user_id, amount, payment_method, payment_status) VALUES (:order_id, :user_id, :amount, :payment_method, :payment_status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $order_id,
                ':user_id' => $user_id,
                ':amount' => $amount,
                ':payment_method' => $payment_method,
                ':payment_status' => $payment_status
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getPaymentByOrderId($order_id)
    {
        $sql = "SELECT * FROM payment WHERE order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Luhn Algorithm for Credit Card Validation
     * Validates if a credit card number is valid using the Luhn checksum algorithm
     * 
     * @param string $cardNumber - The credit card number to validate
     * @return bool - True if valid, false if invalid
     */
    public function luhnCheck($cardNumber)
    {
        // Remove any spaces, hyphens, or other non-digit characters
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Check if the card number is empty or contains non-numeric characters
        if (empty($cardNumber) || !ctype_digit($cardNumber)) {
            return false;
        }
        
        // Check minimum and maximum length (typically 13-19 digits)
        $length = strlen($cardNumber);
        if ($length < 13 || $length > 19) {
            return false;
        }
        
        $sum = 0;
        $alternate = false;
        
        // Process digits from right to left
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = intval($cardNumber[$i]);
            
            if ($alternate) {
                $digit *= 2;
                // If the doubled digit is greater than 9, subtract 9
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        // The card number is valid if the sum is divisible by 10
        return ($sum % 10 === 0);
    }

    /**
     * Detect Credit Card Type based on card number patterns
     * 
     * @param string $cardNumber - The credit card number
     * @return string - The detected card type
     */
    public function detectCardType($cardNumber)
    {
        // Remove any spaces, hyphens, or other non-digit characters
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        $firstDigit = substr($cardNumber, 0, 1);
        $firstTwo = substr($cardNumber, 0, 2);
        $firstThree = substr($cardNumber, 0, 3);
        $firstFour = substr($cardNumber, 0, 4);
        
        // Visa: starts with 4, 13-19 digits
        if ($firstDigit === '4' && (strlen($cardNumber) >= 13 && strlen($cardNumber) <= 19)) {
            return 'Visa';
        }
        
        // Mastercard: starts with 5 (51-55) or 2 (2221-2720), 16 digits
        if (strlen($cardNumber) === 16) {
            $firstTwoNum = intval($firstTwo);
            if (($firstTwoNum >= 51 && $firstTwoNum <= 55) || 
                ($firstTwoNum >= 22 && $firstTwoNum <= 27)) {
                return 'Mastercard';
            }
        }
        
        // American Express: starts with 34 or 37, 15 digits
        if (($firstTwo === '34' || $firstTwo === '37') && strlen($cardNumber) === 15) {
            return 'American Express';
        }
        
        // Discover: starts with 6011, 622126-622925, 644-649, or 65, 16 digits
        if (strlen($cardNumber) === 16) {
            if ($firstFour === '6011' || 
                ($firstThree >= '644' && $firstThree <= '649') || 
                $firstTwo === '65') {
                return 'Discover';
            }
            $firstSix = substr($cardNumber, 0, 6);
            if ($firstSix >= '622126' && $firstSix <= '622925') {
                return 'Discover';
            }
        }
        
        // Diners Club: starts with 300-305, 36, or 38, 14 digits
        if (strlen($cardNumber) === 14) {
            $firstThreeNum = intval($firstThree);
            if (($firstThreeNum >= 300 && $firstThreeNum <= 305) || 
                $firstTwo === '36' || $firstTwo === '38') {
                return 'Diners Club';
            }
        }
        
        // JCB: starts with 35 (3528-3589), 16 digits
        if ($firstTwo === '35' && strlen($cardNumber) === 16) {
            $firstFourNum = intval($firstFour);
            if ($firstFourNum >= 3528 && $firstFourNum <= 3589) {
                return 'JCB';
            }
        }
        
        return 'Unknown';
    }

    /**
     * Comprehensive Card Validation
     * Combines Luhn algorithm with card type validation
     * 
     * @param string $cardNumber - The credit card number to validate
     * @param string $expectedType - Optional: expected card type to validate against
     * @return array - Validation result with details
     */
    public function validateCard($cardNumber, $expectedType = null)
    {
        $result = [
            'valid' => false,
            'card_type' => 'Unknown',
            'errors' => []
        ];
        
        // Remove any spaces, hyphens, or other non-digit characters
        $cleanCardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Check if empty
        if (empty($cleanCardNumber)) {
            $result['errors'][] = 'Card number is required';
            return $result;
        }
        
        // Check if contains only digits
        if (!ctype_digit($cleanCardNumber)) {
            $result['errors'][] = 'Card number must contain only digits';
            return $result;
        }
        
        // Check length
        $length = strlen($cleanCardNumber);
        if ($length < 13 || $length > 19) {
            $result['errors'][] = 'Card number must be between 13 and 19 digits';
            return $result;
        }
        
        // Luhn algorithm validation
        if (!$this->luhnCheck($cleanCardNumber)) {
            $result['errors'][] = 'Invalid card number (failed Luhn check)';
            return $result;
        }
        
        // Detect card type
        $detectedType = $this->detectCardType($cleanCardNumber);
        $result['card_type'] = $detectedType;
        
        // Validate against expected type if provided
        if ($expectedType && strtolower($detectedType) !== strtolower($expectedType)) {
            $result['errors'][] = "Card type mismatch. Expected {$expectedType}, detected {$detectedType}";
            return $result;
        }
        
        // If we reach here, the card is valid
        $result['valid'] = true;
        
        return $result;
    }

    /**
     * Secure method to get card information without storing sensitive data
     * 
     * @param string $cardNumber - The credit card number
     * @return array - Safe card information (last 4 digits, type, etc.)
     */
    public function getSecureCardInfo($cardNumber)
    {
        $cleanCardNumber = preg_replace('/\D/', '', $cardNumber);
        
        return [
            'last_four' => substr($cleanCardNumber, -4),
            'card_type' => $this->detectCardType($cleanCardNumber),
            'is_valid' => $this->luhnCheck($cleanCardNumber),
            'masked_number' => $this->maskCardNumber($cleanCardNumber)
        ];
    }

    /**
     * Mask card number for display purposes
     * 
     * @param string $cardNumber - The credit card number
     * @return string - Masked card number (e.g., **** **** **** 1234)
     */
    public function maskCardNumber($cardNumber)
    {
        $cleanCardNumber = preg_replace('/\D/', '', $cardNumber);
        $length = strlen($cleanCardNumber);
        
        if ($length < 4) {
            return str_repeat('*', $length);
        }
        
        $lastFour = substr($cleanCardNumber, -4);
        $maskedPart = str_repeat('*', $length - 4);
        
        // Format with spaces for readability
        $masked = $maskedPart . $lastFour;
        return chunk_split($masked, 4, ' ');
    }
}
