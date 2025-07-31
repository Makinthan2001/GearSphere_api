-- Add Reviews to GearSphere Database
-- Execute this file separately from the main database dump

USE gearsphere;

-- Check existing users before inserting reviews
SELECT 'Checking existing users...' as Status;
SELECT user_id, name, user_type FROM users WHERE user_id IN (27, 31, 33, 34, 35);

-- Additional System Reviews from different users
INSERT INTO `reviews` (`user_id`, `target_type`, `target_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(27, 'system', NULL, 4, 'As a seller on this platform, I appreciate the professional environment and quality customer base. Great platform for PC component business.', 'approved', '2025-07-26 10:00:00'),
(31, 'system', NULL, 5, 'Being a technician on GearSphere has been rewarding. The platform connects me with customers who truly appreciate quality PC building services.', 'approved', '2025-07-27 14:30:00'),
(33, 'system', NULL, 4, 'Good platform for technicians. Easy to manage appointments and the customer communication system works well. Happy to be part of GearSphere.', 'approved', '2025-07-28 11:15:00'),
(34, 'system', NULL, 5, 'Excellent platform! Both as a technician and occasional customer, the experience has been consistently positive. Well-designed system.', 'approved', '2025-07-29 09:00:00'),
(35, 'system', NULL, 4, 'Great marketplace for PC components and services. The technician assignment system works smoothly and customers are generally well-informed.', 'approved', '2025-07-29 16:45:00');

-- Additional reviews for more variety
INSERT INTO `reviews` (`user_id`, `target_type`, `target_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(32, 'system', NULL, 5, 'Excellent service! The PC building process was smooth and the technician was very professional. Highly recommend GearSphere for custom PC builds.', 'approved', '2025-07-25 10:30:00'),
(40, 'system', NULL, 4, 'Great platform for finding quality PC components. The website is easy to navigate and the customer support is responsive. Will definitely use again.', 'approved', '2025-07-26 14:15:00'),
(42, 'system', NULL, 5, 'Amazing experience! The technician helped me build my dream gaming PC. Everything worked perfectly and the service was top-notch.', 'approved', '2025-07-27 09:45:00'),
(39, 'system', NULL, 4, 'Good selection of components and competitive prices. The delivery was fast and all items were well packaged. Very satisfied with my purchase.', 'approved', '2025-07-28 16:20:00'),
(40, 'system', NULL, 3, 'Good service overall but delivery took longer than expected. The components were genuine and well-packaged though.', 'approved', '2025-07-24 12:30:00');

-- Technician-specific Reviews
INSERT INTO `reviews` (`user_id`, `target_type`, `target_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(42, 'technician', 4, 5, 'Pukaliny was fantastic! Very knowledgeable about workstation builds and completed my project ahead of schedule. Professional and friendly service.', 'approved', '2025-07-25 13:45:00'),
(32, 'technician', 5, 4, 'Suman did an excellent job with my custom water cooling setup. Attention to detail was impressive and the system runs perfectly cool and quiet.', 'approved', '2025-07-26 17:30:00'),
(40, 'technician', 6, 5, 'Demario built an amazing gaming PC for me. Great communication throughout the process and the final result exceeded my expectations. Highly recommend!', 'approved', '2025-07-27 12:15:00'),
(42, 'technician', 7, 4, 'Abinath was very professional and knowledgeable. The gaming PC build was completed efficiently and all components work flawlessly. Good experience overall.', 'approved', '2025-07-28 08:30:00'),
(32, 'technician', 13, 5, 'Madhan provided excellent service for my workstation build. Very thorough in explaining the process and delivered exactly what I needed for my work requirements.', 'approved', '2025-07-29 15:45:00');

-- Show results
SELECT 'Reviews added successfully!' as Status;
SELECT COUNT(*) as 'Total Reviews' FROM reviews;
SELECT target_type, COUNT(*) as count FROM reviews GROUP BY target_type;