-- GYMZONE Database Schema & Seed Data
-- Database: gymzone_db

CREATE DATABASE IF NOT EXISTS `gymzone_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gymzone_db`;

-- Drop tables in order if recreating
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `trial_bookings`;
DROP TABLE IF EXISTS `membership_plans`;
DROP TABLE IF EXISTS `trainers`;
DROP TABLE IF EXISTS `gym_facilities`;
DROP TABLE IF EXISTS `facilities`;
DROP TABLE IF EXISTS `gyms`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'owner', 'admin') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Gyms Table
CREATE TABLE `gyms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `tagline` VARCHAR(255) DEFAULT NULL,
  `description` TEXT NOT NULL,
  `area` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) DEFAULT 'Jamshedpur',
  `state` VARCHAR(50) DEFAULT 'Jharkhand',
  `pincode` VARCHAR(10) DEFAULT '831001',
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `website` VARCHAR(150) DEFAULT NULL,
  `starting_price` DECIMAL(10,2) NOT NULL DEFAULT 999.00,
  `opening_date` DATE NOT NULL,
  `is_newly_opened` TINYINT(1) DEFAULT 0,
  `opening_offer` VARCHAR(255) DEFAULT NULL,
  `has_free_trial` TINYINT(1) DEFAULT 1,
  `timings_weekdays` VARCHAR(100) DEFAULT '6:00 AM - 10:00 PM',
  `timings_weekends` VARCHAR(100) DEFAULT '7:00 AM - 8:00 PM',
  `main_image` VARCHAR(500) NOT NULL,
  `gallery_images` TEXT DEFAULT NULL,
  `google_maps_embed` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  `is_featured` TINYINT(1) DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 4.5,
  `review_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Facilities Table
CREATE TABLE `facilities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'fas fa-dumbbell'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Gym Facilities Mapping
CREATE TABLE `gym_facilities` (
  `gym_id` INT NOT NULL,
  `facility_id` INT NOT NULL,
  PRIMARY KEY (`gym_id`, `facility_id`),
  FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Trainers Table
CREATE TABLE `trainers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `specialization` VARCHAR(150) NOT NULL,
  `experience_years` INT NOT NULL DEFAULT 3,
  `photo` VARCHAR(500) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Membership Plans Table
CREATE TABLE `membership_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `plan_type` ENUM('Monthly', 'Quarterly', 'Half-Yearly', 'Yearly') NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `discounted_price` DECIMAL(10,2) DEFAULT NULL,
  `features` TEXT NOT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Trial Bookings Table
CREATE TABLE `trial_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_ref` VARCHAR(30) NOT NULL UNIQUE,
  `gym_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_email` VARCHAR(100) NOT NULL,
  `user_phone` VARCHAR(20) NOT NULL,
  `visit_date` DATE NOT NULL,
  `time_slot` VARCHAR(50) NOT NULL,
  `fitness_goal` VARCHAR(255) DEFAULT 'General Fitness',
  `status` ENUM('pending', 'confirmed', 'attended', 'cancelled') NOT NULL DEFAULT 'pending',
  `owner_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Reviews Table
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment` TEXT NOT NULL,
  `status` ENUM('approved', 'pending', 'hidden') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Favorites Table
CREATE TABLE `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `gym_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_gym_fav` (`user_id`, `gym_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Contact Messages
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================
-- SEED DATA (Realistic Sample Data for Jamshedpur)
-- Default passwords for all accounts: 'password123'
-- Hashed via password_hash('password123', PASSWORD_BCRYPT)
-- $2y$10$qR6j51o8K.wM1k50oP9zP.l9H0pL7J0L0e8W5y8Z3M.K6d1x3x2.q
-- ===================================================

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `avatar`) VALUES
(1, 'Admin GymZone', 'admin@gymzone.com', '$2y$10$qP1E1nIq3qR7Zz2Y7l9Qe.h7CjYlD.fFz4yXnNqO2m8W3h7FjI5rO', '+91 98765 43210', 'admin', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80'),
(2, 'Vikram Sharma', 'vikram@goldbistupur.com', '$2y$10$qP1E1nIq3qR7Zz2Y7l9Qe.h7CjYlD.fFz4yXnNqO2m8W3h7FjI5rO', '+91 94311 22334', 'owner', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=250&q=80'),
(3, 'Rajesh Mahato', 'rajesh@ironparadise.com', '$2y$10$qP1E1nIq3qR7Zz2Y7l9Qe.h7CjYlD.fFz4yXnNqO2m8W3h7FjI5rO', '+91 93348 11223', 'owner', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=250&q=80'),
(4, 'Pooja Agarwal', 'pooja@pulsefitness.in', '$2y$10$qP1E1nIq3qR7Zz2Y7l9Qe.h7CjYlD.fFz4yXnNqO2m8W3h7FjI5rO', '+91 91223 99887', 'owner', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=250&q=80'),
(5, 'Amit Sen', 'user@gymzone.com', '$2y$10$qP1E1nIq3qR7Zz2Y7l9Qe.h7CjYlD.fFz4yXnNqO2m8W3h7FjI5rO', '+91 98351 77665', 'user', 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=250&q=80');

-- Facilities
INSERT INTO `facilities` (`id`, `name`, `icon`) VALUES
(1, 'Air Conditioned', 'fas fa-snowflake'),
(2, 'Steam & Sauna', 'fas fa-hot-tub'),
(3, 'Certified Personal Trainers', 'fas fa-user-ninja'),
(4, 'Free Parking', 'fas fa-parking'),
(5, 'Locker & Shower', 'fas fa-shower'),
(6, 'CrossFit & Functional Rig', 'fas fa-dumbbell'),
(7, 'Cardio Theatre', 'fas fa-heartbeat'),
(8, 'Juice & Nutrition Bar', 'fas fa-blender'),
(9, 'Open 24/7 Access', 'fas fa-clock'),
(10, 'Zumba & Yoga Studio', 'fas fa-om'),
(11, 'Olympic Free Weights', 'fas fa-weight-hanging'),
(12, 'InBody Body Composition Analysis', 'fas fa-chart-line');

-- Gyms in Jamshedpur
INSERT INTO `gyms` (`id`, `owner_id`, `name`, `slug`, `tagline`, `description`, `area`, `address`, `city`, `state`, `pincode`, `phone`, `email`, `website`, `starting_price`, `opening_date`, `is_newly_opened`, `opening_offer`, `has_free_trial`, `timings_weekdays`, `timings_weekends`, `main_image`, `gallery_images`, `google_maps_embed`, `status`, `is_featured`, `rating`, `review_count`) VALUES
(1, 2, 'Gold’s Gym Bistupur', 'golds-gym-bistupur', 'The Mecca of Bodybuilding & Luxury Fitness in Steel City', 'Experience world-class fitness at Gold\'s Gym Bistupur. Spread across 12,000 sq.ft., our multi-level facility offers top-of-the-line LifeFitness and Hammer Strength equipment, temperature-controlled workout floors, specialized spinning arena, and luxurious locker rooms with steam suites.', 'Bistupur', '3rd Floor, Regal Square, Main Road, Bistupur', 'Jamshedpur', 'Jharkhand', '831001', '+91 657 242 8899', 'bistupur@goldsgym.in', 'https://goldsgym.in', 2499.00, '2024-03-15', 0, 'Complimentary 3-Day VIP Pass + Personal Assessment', 1, '5:30 AM - 10:30 PM', '6:00 AM - 9:00 PM', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=800&q=80"]', 'https://maps.google.com/maps?q=Bistupur+Jamshedpur&t=&z=15&ie=UTF8&iwloc=&output=embed', 'approved', 1, 4.8, 48),

(2, 3, 'Iron Paradise Fitness & CrossFit', 'iron-paradise-fitness-sakchi', 'Raw Power, Calisthenics & Olympic Lifting Hub', 'Recently inaugurated in the heart of Sakchi, Iron Paradise is built for serious fitness enthusiasts, athletes, and lifters. Equipped with calibrated Rogue competition plates, custom power cages, a 40ft turf runway for sled pushes, and dedicated calisthenics rigs.', 'Sakchi', 'Plot 42, Super Centre Complex, Sakchi Market Road', 'Jamshedpur', 'Jharkhand', '831001', '+91 93348 11223', 'contact@ironparadise.com', 'https://ironparadise.com', 1499.00, '2026-08-10', 1, 'GRAND OPENING: Flat 40% OFF on Annual Plans + Free Shaker Bottle', 1, '5:00 AM - 11:00 PM', '6:00 AM - 10:00 PM', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80"]', 'https://maps.google.com/maps?q=Sakchi+Jamshedpur&t=&z=15&ie=UTF8&iwloc=&output=embed', 'approved', 1, 4.9, 32),

(3, 4, 'Pulse Fitness & Wellness Sanctuary', 'pulse-fitness-circuit-house', 'Boutique Wellness, Yoga, Pilates & High-Energy HIIT', 'A brand new sanctuary for holistic fitness located in the peaceful Circuit House Area. Pulse Fitness combines cutting-edge cardio with serene reformer Pilates, Hot Yoga sessions, and an organic nutrition bar serving cold-pressed juices and post-workout smoothies.', 'Circuit House Area', 'Bungalow 18, Straight Mile Road, Circuit House Area', 'Jamshedpur', 'Jharkhand', '831001', '+91 91223 99887', 'hello@pulsefitness.in', 'https://pulsefitness.in', 1999.00, '2026-09-01', 1, 'NEW OPENING SPECIAL: Free 1-Week Trial & Nutrition Consultation', 1, '6:00 AM - 9:30 PM', '7:00 AM - 7:00 PM', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1575052814086-f385e2e2ad1b?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&w=800&q=80"]', 'https://maps.google.com/maps?q=Circuit+House+Area+Jamshedpur&t=&z=15&ie=UTF8&iwloc=&output=embed', 'approved', 1, 4.7, 24),

(4, 2, 'Steel City CrossFit & Calisthenics', 'steel-city-crossfit-kadma', 'Jamshedpur’s Premier Official CrossFit Community', 'Designed for high-intensity functional training, gymnastics, and barbell conditioning. Build real strength and cardiovascular stamina with our daily WODs (Workout of the Day) led by certified Level 2 CrossFit coaches in a spacious open-concept warehouse gym.', 'Kadma', 'Opposite Kadma Police Station, Marine Drive Link Road', 'Jamshedpur', 'Jharkhand', '831005', '+91 657 230 4455', 'coach@steelcitycrossfit.com', 'https://steelcitycrossfit.com', 1299.00, '2025-01-20', 0, 'First WOD Free for Jamshedpur Residents', 1, '6:00 AM - 9:00 PM', '7:00 AM - 12:00 PM', 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1534367507873-d2d7e24c797f?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?auto=format&fit=crop&w=800&q=80"]', 'https://maps.google.com/maps?q=Kadma+Jamshedpur&t=&z=15&ie=UTF8&iwloc=&output=embed', 'approved', 0, 4.6, 61),

(5, 3, 'Titans Health Club & Spa', 'titans-health-club-sonari', 'Family Fitness, Swimming, Aerobics & Strength', 'A comprehensive fitness destination in Sonari offering strength training, cardio machinery, dedicated women-only workout hours, Zumba dance studios, and rejuvenating steam baths.', 'Sonari', 'Near Aerodrome, Inner Circle Road, Sonari', 'Jamshedpur', 'Jharkhand', '831011', '+91 657 231 7766', 'info@titansgym.in', 'https://titansgym.in', 1699.00, '2025-05-12', 0, 'Get 2 Months Extra on Half-Yearly Subscriptions', 1, '6:00 AM - 10:00 PM', '6:30 AM - 8:30 PM', 'https://images.unsplash.com/photo-1576678927484-cc907957088c?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&w=800&q=80"]', 'https://maps.google.com/maps?q=Sonari+Jamshedpur&t=&z=15&ie=UTF8&iwloc=&output=embed', 'approved', 0, 4.5, 39),

(6, 4, 'Elite 24/7 Fitness Center', 'elite-247-fitness-telco', '24/7 Access, Smart Biometric Entry & Hammer Strength', 'Newly opened in Telco Colony, catering to shift engineers and busy professionals. Never miss a workout with 24 hours biometric entry, smart cardio consoles, ISO-lateral chest presses, and dedicated free weight zones.', 'Telco', 'Sector 4 Commercial Plaza, Near Telco Club, Telco', 'Jamshedpur', 'Jharkhand', '831004', '+91 94301 55667', 'telco@elite247fitness.com', 'https://elite247fitness.com', 1799.00, '2026-09-18', 1, 'NEW OPENING: Zero Admission Fee + Free Fitness Tracker on Annual Plan', 1, 'Open 24 Hours / 7 Days', 'Open 24 Hours / 7 Days', 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80"]', 'https://maps.google.com/maps?q=Telco+Jamshedpur&t=&z=15&ie=UTF8&iwloc=&output=embed', 'approved', 1, 4.8, 19);

-- Facility Mappings
INSERT INTO `gym_facilities` (`gym_id`, `facility_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 7), (1, 10), (1, 12),
(2, 1), (2, 3), (2, 4), (2, 5), (2, 6), (2, 8), (2, 11),
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 8), (3, 10), (3, 12),
(4, 3), (4, 4), (4, 6), (4, 11),
(5, 1), (5, 2), (5, 3), (5, 4), (5, 5), (5, 7), (5, 10),
(6, 1), (6, 3), (6, 4), (6, 5), (6, 7), (6, 9), (6, 11);

-- Trainers
INSERT INTO `trainers` (`gym_id`, `name`, `specialization`, `experience_years`, `photo`, `bio`) VALUES
(1, 'Rohan Roy', 'Bodybuilding & Hypertrophy Specialist', 8, 'https://images.unsplash.com/photo-1567013127542-490d757e51fc?auto=format&fit=crop&w=400&q=80', 'Former Mr. Jharkhand champion, specialized in contest prep and lean muscle hypertrophy.'),
(1, 'Sneha Mukherjee', 'Functional Training & Aerobics', 5, 'https://images.unsplash.com/photo-1594381898411-846e7d193883?auto=format&fit=crop&w=400&q=80', 'ACE Certified trainer passionate about core conditioning, mobility, and high-energy spin.'),
(2, 'Gurpreet Singh', 'Powerlifting & Strongman Coach', 7, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=400&q=80', 'National level powerlifter. Teaches squat, bench, and deadlift mechanics with injury prevention.'),
(2, 'Karan Verma', 'Calisthenics & Movement Specialist', 4, 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=400&q=80', 'Bodyweight mastery expert helping members master muscle-ups, handstands, and levers.'),
(3, 'Ananya Das', 'Hatha Yoga & Postural Alignment', 6, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=400&q=80', 'Certified 500-hour Yoga Alliance instructor focusing on breathwork, mindfulness, and flexibility.'),
(6, 'Devendra Patel', 'Fat Loss & Strength Conditioning', 6, 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=400&q=80', 'Expert in customized nutritional guidance and time-efficient metabolic circuit training.');

-- Membership Plans
INSERT INTO `membership_plans` (`gym_id`, `plan_type`, `title`, `price`, `discounted_price`, `features`, `is_popular`) VALUES
-- Gym 1: Gold's Gym Bistupur
(1, 'Monthly', 'Standard Monthly Pass', 2999.00, 2499.00, 'Full Gym Floor Access\nLocker & Shower Facility\n1 Free Fitness Assessment\nCardio & Strength Machines', 0),
(1, 'Quarterly', 'Quarterly Muscle Builder', 8499.00, 6999.00, 'Everything in Monthly\nSteam & Sauna Access\n2 Personal Training Tasters\nDiet Chart Consultation', 0),
(1, 'Half-Yearly', 'Pro Transformation Plan', 15999.00, 12999.00, 'Everything in Quarterly\nUnlimited Spinning Sessions\nFree Gold\'s Gym Merchandise\nBody Composition Tracking', 1),
(1, 'Yearly', 'Gold VIP Annual Elite', 28999.00, 21999.00, 'All-Access VIP Passport\nGuest Passes (4/year)\nFull Steam & Sauna Suite\nPersonal Locker Priority\nFree Nutrition Consultation', 0),

-- Gym 2: Iron Paradise
(2, 'Monthly', 'Raw Iron Monthly', 1799.00, 1499.00, 'Olympic Weightlifting Deck\nCalisthenics Zone Access\nLocker Room Access\nCommunity Workout Access', 0),
(2, 'Quarterly', 'Strength Forge 90', 4999.00, 3999.00, 'All Monthly Perks\nPowerlifting Coaching Tips\nSled & Turf Track Access\nIron Paradise T-Shirt', 1),
(2, 'Yearly', 'Titan Brotherhood Annual', 17999.00, 11999.00, 'Full 365 Days Access\nZero Registration Fee\n2 Free Friend Passes/month\nDiscount at Nutrition Bar', 0),

-- Gym 3: Pulse Fitness
(3, 'Monthly', 'Wellness Discovery', 2499.00, 1999.00, 'Gym Floor & Cardio\n2 Yoga / Pilates Classes\nFree InBody Scan\nJuice Bar Discount (10%)', 0),
(3, 'Yearly', 'Harmonious Lifestyle Annual', 22999.00, 16999.00, 'Unlimited Yoga & Pilates\nFull Gym Access\nSauna & Recovery Lounge\nQuarterly Dietician Session', 1);

-- Trial Bookings
INSERT INTO `trial_bookings` (`booking_ref`, `gym_id`, `user_id`, `user_name`, `user_email`, `user_phone`, `visit_date`, `time_slot`, `fitness_goal`, `status`, `owner_notes`) VALUES
('GZ-2026-9182', 1, 5, 'Amit Sen', 'user@gymzone.com', '+91 98351 77665', '2026-10-05', '06:00 AM - 08:00 AM', 'Fat loss and strength training', 'confirmed', 'Assigned trainer Rohan Roy for initial tour'),
('GZ-2026-4431', 2, NULL, 'Sunil Soren', 'sunil.soren@gmail.com', '+91 94315 22001', '2026-10-06', '06:00 PM - 08:00 PM', 'Powerlifting and deadlift form check', 'pending', NULL),
('GZ-2026-7821', 3, NULL, 'Megha Kumari', 'megha.k@outlook.com', '+91 91234 56789', '2026-10-04', '08:00 AM - 10:00 AM', 'Yoga and core flexibility', 'attended', 'Joined quarterly plan on spot');

-- Reviews
INSERT INTO `reviews` (`gym_id`, `user_id`, `user_name`, `rating`, `comment`, `status`) VALUES
(1, 5, 'Amit Sen', 5, 'Gold\'s Gym Bistupur is hands down the most luxurious and well-equipped gym in Jamshedpur! The steam room after a brutal leg session is unmatched.', 'approved'),
(1, NULL, 'Ritu Sharma', 5, 'Very safe, respectful environment for female lifters. The trainers actually pay attention to posture.', 'approved'),
(2, NULL, 'Manish Tripathy', 5, 'Best powerlifting gym in Sakchi. Heavy dumbbells up to 60kg and real Rogue barbells. Absolutely love the energetic music!', 'approved'),
(3, NULL, 'Neha Verma', 5, 'The yoga sessions by Ananya are so peaceful and revitalizing. Cleanest showers and lovely ambience in Circuit House area.', 'approved'),
(6, NULL, 'Deepak Pandey', 5, 'Working in Telco shifts makes a 24/7 gym a blessing. Biometric punch-in at 11 PM feels super secure and comfortable.', 'approved');

-- Favorites
INSERT INTO `favorites` (`user_id`, `gym_id`) VALUES
(5, 1),
(5, 2);
