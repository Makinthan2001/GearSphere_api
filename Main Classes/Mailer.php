<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Import PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'madhan2001ana@gmail.com'; // SMTP email
        $this->mail->Password   = 'ilergdrkkdycocoh'; // SMTP password
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port       = 465;
        
        $this->mail->setFrom('madhan2001ana@gmail.com', 'GearSphere');
    }

    // Enhanced method with neat templates
    public function setInfo($recipientEmail, $subject, $message) {
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
    }

    // OTP Email Template
    public function sendOTPEmail($recipientEmail, $userName, $otp, $purpose = 'verification') {
        $subject = "GearSphere - Email Verification Code";
        $message = $this->getOTPTemplate($userName, $otp, $purpose);
        
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
    }

    // // Welcome Email Template
    // public function sendWelcomeEmail($recipientEmail, $userName, $userType = 'customer') {
    //     $subject = "Welcome to GearSphere - Let's Build Your Dream PC!";
    //     $message = $this->getWelcomeTemplate($userName, $userType);
        
    //     $this->mail->addAddress($recipientEmail);
    //     $this->mail->isHTML(true);
    //     $this->mail->Subject = $subject;
    //     $this->mail->Body = $message;
    // }

    // Order Confirmation Email Template
    // public function sendOrderConfirmationEmail($recipientEmail, $userName, $orderDetails) {
    //     $subject = "GearSphere - Order Confirmation #{$orderDetails['order_id']}";
    //     $message = $this->getOrderConfirmationTemplate($userName, $orderDetails);
        
    //     $this->mail->addAddress($recipientEmail);
    //     $this->mail->isHTML(true);
    //     $this->mail->Subject = $subject;
    //     $this->mail->Body = $message;
    // }

    // Order Status Update Email Template
    // public function sendOrderStatusEmail($recipientEmail, $userName, $orderDetails, $newStatus) {
    //     $subject = "GearSphere - Order Update #{$orderDetails['order_id']}";
    //     $message = $this->getOrderStatusTemplate($userName, $orderDetails, $newStatus);
        
    //     $this->mail->addAddress($recipientEmail);
    //     $this->mail->isHTML(true);
    //     $this->mail->Subject = $subject;
    //     $this->mail->Body = $message;
    // }

    // Password Reset Email Template
    public function sendPasswordResetEmail($recipientEmail, $userName, $otp) {
        $subject = "GearSphere - Password Reset Request";
        $message = $this->getPasswordResetTemplate($userName, $otp);
        
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
    }

    // Technician Application Email Template
    // public function sendTechnicianApplicationEmail($recipientEmail, $userName, $status) {
    //     $subject = "GearSphere - Technician Application Update";
    //     $message = $this->getTechnicianApplicationTemplate($userName, $status);
        
    //     $this->mail->addAddress($recipientEmail);
    //     $this->mail->isHTML(true);
    //     $this->mail->Subject = $subject;
    //     $this->mail->Body = $message;
    // }

    // Technician Assignment Email Template
    public function sendTechnicianAssignmentEmail($recipientEmail, $technicianName, $assignmentDetails) {
        $subject = "GearSphere - New PC Build Assignment";
        $message = $this->getTechnicianAssignmentTemplate($technicianName, $assignmentDetails);
        
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
    }

    // Build Request Status Email Template
    public function sendBuildRequestStatusEmail($recipientEmail, $customerName, $status, $technicianName = '') {
        $subject = "GearSphere - Build Request Update";
        $message = $this->getBuildRequestStatusTemplate($customerName, $status, $technicianName);
        
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
    }

    // Account Status Email Template
    public function sendAccountStatusEmail($recipientEmail, $userName, $status) {
        $subject = "GearSphere - Account Status Update";
        $message = $this->getAccountStatusTemplate($userName, $status);
        
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
    }

    // Base HTML Template
    private function getBaseTemplate($content) {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>GearSphere</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                .logo {
                    font-size: 28px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    letter-spacing: 1px;
                }
                .tagline {
                    font-size: 14px;
                    opacity: 0.9;
                }
                .content {
                    padding: 40px 30px;
                }
                .greeting {
                    font-size: 18px;
                    margin-bottom: 20px;
                    color: #2c3e50;
                }
                .message {
                    font-size: 16px;
                    line-height: 1.8;
                    margin-bottom: 30px;
                    color: #555;
                }
                .cta-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: bold;
                    margin: 20px 0;
                    transition: transform 0.3s ease;
                }
                .cta-button:hover {
                    transform: translateY(-2px);
                }
                .otp-box {
                    background-color: #f8f9fa;
                    border: 2px dashed #667eea;
                    border-radius: 10px;
                    padding: 20px;
                    text-align: center;
                    margin: 20px 0;
                }
                .otp-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #667eea;
                    letter-spacing: 5px;
                    margin: 10px 0;
                }
                .footer {
                    background-color: #2c3e50;
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                .contact-info {
                    margin: 15px 0;
                    font-size: 14px;
                }
                .disclaimer {
                    font-size: 12px;
                    opacity: 0.8;
                    margin-top: 20px;
                    line-height: 1.5;
                }
                .order-details {
                    background-color: #f8f9fa;
                    border-radius: 10px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .order-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #dee2e6;
                }
                .order-item:last-child {
                    border-bottom: none;
                    font-weight: bold;
                    color: #667eea;
                }
                .status-badge {
                    display: inline-block;
                    padding: 5px 15px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .status-pending { background-color: #fff3cd; color: #856404; }
                .status-confirmed { background-color: #d1ecf1; color: #0c5460; }
                .status-processing { background-color: #d4edda; color: #155724; }
                .status-shipped { background-color: #cce7ff; color: #004085; }
                .status-delivered { background-color: #d1f2eb; color: #0c6e54; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='logo'>🛠️ GearSphere</div>
                    <div class='tagline'>Building the Future, One Custom PC at a Time</div>
                </div>
                <div class='content'>
                    $content
                </div>
                <div class='footer'>
                    <div class='contact-info'>
                        <strong>Contact Us:</strong><br>
                        📧 info@gearsphere.com | support@gearsphere.com<br>
                        📱 +94 (76) 375 3730 | +94 (70) 407 9547<br>
                        📍 Pasara Road, Badulla City, 90 000
                    </div>
                    <div class='disclaimer'>
                        This email was sent to you because you have an account with GearSphere.<br>
                        If you believe this was sent in error, please contact our support team.<br>
                        © " . date('Y') . " GearSphere. All rights reserved.
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }

    // OTP Email Template
    private function getOTPTemplate($userName, $otp, $purpose) {
        $purposeText = $purpose === 'password_reset' ? 'reset your password' : 'verify your email address';
        $content = "
            <div class='greeting'>Hello " . htmlspecialchars($userName) . "! 👋</div>
            <div class='message'>
                We received a request to $purposeText for your GearSphere account. Please use the verification code below:
            </div>
            <div class='otp-box'>
                <div style='font-size: 14px; color: #666; margin-bottom: 10px;'>Your Verification Code</div>
                <div class='otp-code'>$otp</div>
                <div style='font-size: 12px; color: #999; margin-top: 10px;'>This code expires in 10 minutes</div>
            </div>
            <div class='message'>
                ⚠️ <strong>Security Notice:</strong> Never share this code with anyone. GearSphere staff will never ask for your verification code.
            </div>
            <div class='message'>
                If you didn't request this verification, please ignore this email or contact our support team if you have concerns.
            </div>";
        
        return $this->getBaseTemplate($content);
    }

    // Welcome Email Template
    // private function getWelcomeTemplate($userName, $userType) {
    //     $typeSpecific = $userType === 'technician' 
    //         ? "As a technician, you'll help customers build their dream PCs and provide expert technical support."
    //         : "Get ready to explore our vast collection of PC components and build your dream setup!";
            
    //     $content = "
    //         <div class='greeting'>Welcome to GearSphere, " . htmlspecialchars($userName) . "! 🎉</div>
    //         <div class='message'>
    //             Thank you for joining the GearSphere community! We're excited to have you on board.
    //         </div>
    //         <div class='message'>
    //             $typeSpecific
    //         </div>
    //         <div class='message'>
    //             <strong>What's next?</strong><br>
    //             🔧 Explore our premium PC components<br>
    //             💡 Use our PC Builder tool for custom builds<br>
    //             📞 Connect with our expert technicians<br>
    //             🎯 Track your orders and build progress
    //         </div>
    //         <div class='message'>
    //             Need help getting started? Our support team is here to assist you 24/7!
    //         </div>";
        
    //     return $this->getBaseTemplate($content);
    // }

    // Order Confirmation Template
    // private function getOrderConfirmationTemplate($userName, $orderDetails) {
    //     $items = '';
    //     foreach ($orderDetails['items'] as $item) {
    //         $items .= "
    //             <div class='order-item'>
    //                 <span>" . htmlspecialchars($item['name']) . " (Qty: {$item['quantity']})</span>
    //                 <span>LKR " . number_format($item['price'] * $item['quantity'], 2) . "</span>
    //             </div>";
    //     }
        
    //     $content = "
    //         <div class='greeting'>Order Confirmed! 🎉</div>
    //         <div class='message'>
    //             Hi " . htmlspecialchars($userName) . ",<br><br>
    //             Great news! Your order has been successfully confirmed and is now being processed.
    //         </div>
    //         <div class='order-details'>
    //             <h3 style='color: #667eea; margin-bottom: 15px;'>📦 Order Details</h3>
    //             <div style='margin-bottom: 15px;'>
    //                 <strong>Order ID:</strong> #{$orderDetails['order_id']}<br>
    //                 <strong>Order Date:</strong> " . date('F j, Y') . "<br>
    //                 <strong>Status:</strong> <span class='status-badge status-confirmed'>Confirmed</span>
    //             </div>
    //             $items
    //             <div class='order-item'>
    //                 <span><strong>Total Amount</strong></span>
    //                 <span><strong>LKR " . number_format($orderDetails['total'], 2) . "</strong></span>
    //             </div>
    //         </div>
    //         <div class='message'>
    //             We'll keep you updated via email as your order progresses. You can also track your order status anytime on our website.
    //         </div>";
        
    //     return $this->getBaseTemplate($content);
    // }

    // Order Status Update Template
    // private function getOrderStatusTemplate($userName, $orderDetails, $newStatus) {
    //     $statusMessages = [
    //         'processing' => '⚙️ Your order is now being processed by our team.',
    //         'shipped' => '🚚 Great news! Your order has been shipped and is on its way.',
    //         'delivered' => '✅ Your order has been delivered successfully!',
    //         'cancelled' => '❌ Your order has been cancelled.'
    //     ];
        
    //     $statusClass = "status-$newStatus";
    //     $message = $statusMessages[$newStatus] ?? 'Your order status has been updated.';
        
    //     $content = "
    //         <div class='greeting'>Order Update 📋</div>
    //         <div class='message'>
    //             Hi " . htmlspecialchars($userName) . ",<br><br>
    //             $message
    //         </div>
    //         <div class='order-details'>
    //             <h3 style='color: #667eea; margin-bottom: 15px;'>📦 Order Information</h3>
    //             <div>
    //                 <strong>Order ID:</strong> #{$orderDetails['order_id']}<br>
    //                 <strong>Current Status:</strong> <span class='status-badge $statusClass'>" . ucfirst($newStatus) . "</span><br>
    //                 <strong>Order Total:</strong> LKR " . number_format($orderDetails['total'], 2) . "
    //             </div>
    //         </div>";
        
    //     return $this->getBaseTemplate($content);
    // }

    // Password Reset Template
    private function getPasswordResetTemplate($userName, $otp) {
        return $this->getOTPTemplate($userName, $otp, 'password_reset');
    }

    // Technician Application Template
    // private function getTechnicianApplicationTemplate($userName, $status) {
    //     $statusMessages = [
    //         'approved' => [
    //             'icon' => '✅',
    //             'title' => 'Application Approved!',
    //             'message' => 'Congratulations! Your technician application has been approved. You can now start helping customers with their PC builds.'
    //         ],
    //         'rejected' => [
    //             'icon' => '❌',
    //             'title' => 'Application Update',
    //             'message' => 'Thank you for your interest in becoming a GearSphere technician. Unfortunately, we cannot approve your application at this time.'
    //         ],
    //         'pending' => [
    //             'icon' => '⏳',
    //             'title' => 'Application Received',
    //             'message' => 'We have received your technician application and it is currently under review. We will notify you once a decision has been made.'
    //         ]
    //     ];
        
    //     $statusInfo = $statusMessages[$status];
        
    //     $content = "
    //         <div class='greeting'>{$statusInfo['icon']} {$statusInfo['title']}</div>
    //         <div class='message'>
    //             Hi " . htmlspecialchars($userName) . ",<br><br>
    //             {$statusInfo['message']}
    //         </div>";
        
    //     return $this->getBaseTemplate($content);
    // }

    // Technician Assignment Template
    private function getTechnicianAssignmentTemplate($technicianName, $assignmentDetails) {
        $content = "
            <div class='greeting'>New PC Build Assignment! 🎯</div>
            <div class='message'>
                Hi " . htmlspecialchars($technicianName) . ",<br><br>
                You have been assigned a new PC build project. Here are the details:
            </div>
            <div class='order-details'>
                <h3 style='color: #667eea; margin-bottom: 15px;'>🛠️ Assignment Details</h3>
                <div style='margin-bottom: 15px;'>
                    <strong>Assignment ID:</strong> #{$assignmentDetails['assignment_id']}<br>
                    <strong>Customer Name:</strong> {$assignmentDetails['customer_name']}<br>
                    <strong>Customer Email:</strong> {$assignmentDetails['customer_email']}<br>
                    <strong>Assignment Date:</strong> {$assignmentDetails['date']}
                </div>
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4 style='color: #856404; margin-bottom: 10px;'>📝 Special Instructions:</h4>
                    <p style='margin: 0; color: #856404;'>" . htmlspecialchars($assignmentDetails['instructions']) . "</p>
                </div>
            </div>
            <div class='message'>
                Please log in to your technician dashboard to view complete build specifications and customer contact information. Contact support if you have any questions.
            </div>";
        
        return $this->getBaseTemplate($content);
    }

    // Build Request Status Template
    private function getBuildRequestStatusTemplate($customerName, $status, $technicianName) {
        $statusMessages = [
            'accepted' => '✅ Your build request has been accepted by our technician team.',
            'in_progress' => '⚙️ Your build is currently in progress. Our technicians are working hard to complete it.',
            'completed' => '🎉 Great news! Your build request has been completed successfully.',
            'rejected' => '❌ Unfortunately, your build request has been rejected.',
            'on_hold' => '⏸️ Your build request is currently on hold. Please contact support for more information.'
        ];
        
        $statusClass = "status-$status";
        $message = $statusMessages[$status] ?? 'The status of your build request has been updated.';
        
        $content = "
            <div class='greeting'>Build Request Update 🔔</div>
            <div class='message'>
                Hi " . htmlspecialchars($customerName) . ",<br><br>
                $message
            </div>
            <div class='order-details'>
                <h3 style='color: #667eea; margin-bottom: 15px;'>🛠️ Build Request Information</h3>
                <div>
                    <strong>Current Status:</strong> <span class='status-badge $statusClass'>" . ucfirst($status) . "</span><br>
                    <strong>Assigned Technician:</strong> " . htmlspecialchars($technicianName) . "<br>
                    <strong>Update Date:</strong> " . date('F j, Y') . "
                </div>
            </div>";
        
        return $this->getBaseTemplate($content);
    }

    // Account Status Template
    private function getAccountStatusTemplate($userName, $status) {
        $statusMessages = [
            'disabled' => [
                'icon' => '🚫',
                'title' => 'Account Temporarily Disabled',
                'message' => 'Your GearSphere account has been temporarily disabled due to policy violations or security concerns.',
                'color' => '#dc3545'
            ],
            'enabled' => [
                'icon' => '✅',
                'title' => 'Account Reactivated',
                'message' => 'Great news! Your GearSphere account has been reactivated and you can now access all features.',
                'color' => '#28a745'
            ],
            'suspended' => [
                'icon' => '⏸️',
                'title' => 'Account Suspended',
                'message' => 'Your GearSphere account has been suspended pending review. Please contact support for more information.',
                'color' => '#ffc107'
            ]
        ];
        
        $statusInfo = $statusMessages[$status] ?? [
            'icon' => '🔔',
            'title' => 'Account Status Update',
            'message' => 'Your account status has been updated.',
            'color' => '#6c757d'
        ];
        
        $supportSection = $status === 'disabled' || $status === 'suspended' ? "
            <div class='order-details' style='border-left: 4px solid {$statusInfo['color']};'>
                <h3 style='color: {$statusInfo['color']}; margin-bottom: 15px;'>📞 Need Help?</h3>
                <div style='margin-bottom: 15px;'>
                    If you believe this action was taken in error or if you have questions about your account status, please contact our support team:
                </div>
                <div>
                    <strong>Email:</strong> support@gearsphere.com<br>
                    <strong>Phone:</strong> +94 (76) 375 3730<br>
                    <strong>Response Time:</strong> Within 24 hours
                </div>
            </div>" : "";
        
        $content = "
            <div class='greeting'>{$statusInfo['icon']} {$statusInfo['title']}</div>
            <div class='message'>
                Hi " . htmlspecialchars($userName) . ",<br><br>
                {$statusInfo['message']}
            </div>
            <div class='order-details'>
                <h3 style='color: {$statusInfo['color']}; margin-bottom: 15px;'>📋 Account Information</h3>
                <div>
                    <strong>Account Status:</strong> <span style='color: {$statusInfo['color']}; font-weight: bold; text-transform: uppercase;'>" . ucfirst($status) . "</span><br>
                    
                </div>
            </div>
            $supportSection
            <div class='message'>
                We appreciate your understanding and look forward to serving you better in the future.
            </div>";
        
        return $this->getBaseTemplate($content);
    }

    // Send the email
    public function send() {
        try {
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
