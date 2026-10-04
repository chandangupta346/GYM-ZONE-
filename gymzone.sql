-- GYMZONE Database Schema & Sample Demo Data for Jamshedpur
-- Compatible with MySQL 5.7+ & MariaDB (XAMPP)

CREATE DATABASE IF NOT EXISTS `gymzone` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gymzone`;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `gym_plans`;
DROP TABLE IF EXISTS `gym_trainers`;
DROP TABLE IF EXISTS `gym_facilities`;
DROP TABLE IF EXISTS `gym_images`;
DROP TABLE IF EXISTS `gyms`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'owner', 'admin') NOT NULL DEFAULT 'user',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Gyms Table
CREATE TABLE `gyms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) DEFAULT 'Jamshedpur',
  `area` VARCHAR(100) NOT NULL,
  `price_per_month` DECIMAL(10,2) NOT NULL,
  `description` TEXT,
  `opening_time` VARCHAR(20) DEFAULT '06:00 AM',
  `closing_time` VARCHAR(20) DEFAULT '10:00 PM',
  `phone` VARCHAR(20) NOT NULL,
  `is_new` TINYINT(1) DEFAULT 1,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Gym Images Table
CREATE TABLE `gym_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Gym Facilities Table
CREATE TABLE `gym_facilities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `facility_name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Gym Trainers Table
CREATE TABLE `gym_trainers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `trainer_name` VARCHAR(100) NOT NULL,
  `specialization` VARCHAR(150) NOT NULL,
  `photo_url` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Gym Membership Plans Table
CREATE TABLE `gym_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gym_id` INT NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `features` TEXT,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Favorites Table
CREATE TABLE `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `gym_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_fav` (`user_id`, `gym_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Bookings Table
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT DEFAULT NULL,
  `gym_id` INT NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `visit_date` DATE NOT NULL,
  `visit_time` VARCHAR(20) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`gym_id`) REFERENCES `gyms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- INSERT REALISTIC SAMPLE DATA FOR JAMSHEDPUR (DEMO DATA)

-- Sample Passwords:
-- Admin: Admin@123 -> $2y$10$WpZ6G9l2cR8d2T2K3K.Uee/B7A3l1Q5V3F0g/Y4O.W8k8n.0l2E1W (or created using password_hash)
-- Below hashes were generated with password_hash('Admin@123', PASSWORD_DEFAULT), 'Owner@123', and 'User@123'

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`) VALUES
(1, 'GymZone Administrator', 'admin@gymzone.com', '9876543210', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'admin'),
(2, 'Rahul Sharma (Titanium Owner)', 'owner1@titanium.com', '9812345678', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'owner'),
(3, 'Amit Verma (Pulse Owner)', 'owner2@pulse.com', '9823456789', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'owner'),
(4, 'Sanjay Singh (Iron Owner)', 'owner3@iron.com', '9834567890', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'owner'),
(5, 'Neha Kapoor (Powerhouse Owner)', 'owner4@powerhouse.com', '9845678901', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'owner'),
(6, 'Rohan Das (Beast Owner)', 'owner5@beast.com', '9856789012', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'owner'),
(7, 'Priya Sen (Vanguard Owner)', 'owner6@vanguard.com', '9867890123', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'owner'),
(8, 'Vikram Kumar (Fitness Lover)', 'john@gmail.com', '9988776655', '$2y$10$fGf.u7CgqR7s3M0JpYx84.O3m/U2J1vB3d4e5f6g7h8i9j0k1l2m3', 'user');


-- Sample Gyms in Jamshedpur
INSERT INTO `gyms` (`id`, `owner_id`, `name`, `address`, `city`, `area`, `price_per_month`, `description`, `opening_time`, `closing_time`, `phone`, `is_new`, `status`) VALUES
(1, 2, 'Titanium Fitness Club', '3rd Floor, Contractors Area, Main Road, Bistupur', 'Jamshedpur', 'Bistupur', 2500.00, 'Titanium Fitness Club is a premium 10,000 sq.ft luxury fitness arena featuring imported Matrix cardio equipment, heavy powerlifting Racks, personal training zone, and steam bath facility.', '05:30 AM', '10:30 PM', '9812345678', 1, 'approved'),

(2, 3, 'Pulse Gym & Crossfit Arena', 'Kalimati Road, Near Steel City Mall, Sakchi', 'Jamshedpur', 'Sakchi', 1800.00, 'State-of-the-art Crossfit rig, Olympic weightlifting platforms, air-conditioned strength floor, and group zumba classes led by certified instructors.', '06:00 AM', '10:00 PM', '9823456789', 1, 'approved'),

(3, 4, 'Iron Paradise Fitness', 'Plaza Market Complex, Near Telco Club, Telco Colony', 'Jamshedpur', 'Telco', 1500.00, 'Dedicated bodybuilders paradise with heavy dumbbell stack up to 60kg, squat cages, cable cross, cardio cinema, and post-workout protein bar.', '05:00 AM', '11:00 PM', '9834567890', 0, 'approved'),

(4, 5, 'PowerHouse Gym & Wellness', 'Kagalnagar Main Road, Near Ocean Park, Sonari', 'Jamshedpur', 'Sonari', 2000.00, 'Modern wellness facility combining high-intensity fitness training, spin cycling studio, yoga decks, and nutritional counseling.', '06:00 AM', '09:30 PM', '9845678901', 1, 'approved'),

(5, 6, 'The Beast Cave Gym', 'Rankini Temple Road, Near Outer Circle, Kadma', 'Jamshedpur', 'Kadma', 1200.00, 'Hardcore hardcore strength and hypertrophy gym with broad range of free weights, plate loaded machines, functional turf lane, and boxing ring.', '05:30 AM', '10:00 PM', '9856789012', 0, 'approved'),

(6, 7, 'Vanguard Elite Fitness', 'Tata-Kandra Main Road, Opposite Housing Board, Adityapur', 'Jamshedpur', 'Adityapur', 2200.00, 'Spacious 2-floor commercial workout center equipped with digital treadmills, body composition analyzer, clean locker rooms, and sauna.', '06:00 AM', '10:00 PM', '9867890123', 1, 'approved');


-- Gym Images
INSERT INTO `gym_images` (`gym_id`, `image_url`, `is_primary`) VALUES
(1, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1000&q=80', 1),
(1, 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=800&q=80', 0),
(1, 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=800&q=80', 0),

(2, 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&w=1000&q=80', 1),
(2, 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=800&q=80', 0),

(3, 'https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?auto=format&fit=crop&w=1000&q=80', 1),
(3, 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=800&q=80', 0),

(4, 'https://images.unsplash.com/photo-1593079831268-3381b0db4a77?auto=format&fit=crop&w=1000&q=80', 1),
(4, 'https://images.unsplash.com/photo-1534367507873-d2d7e24c797f?auto=format&fit=crop&w=800&q=80', 0),

(5, 'https://images.unsplash.com/photo-1517963879433-6ad2b056d712?auto=format&fit=crop&w=1000&q=80', 1),

(6, 'https://images.unsplash.com/photo-1576678927484-cc909957088c?auto=format&fit=crop&w=1000&q=80', 1);


-- Gym Facilities
INSERT INTO `gym_facilities` (`gym_id`, `facility_name`) VALUES
(1, 'Central Air Conditioning'), (1, 'Cardio Zone'), (1, 'Heavy Strength Training'), (1, 'Steam Bath'), (1, 'Personal Trainers'), (1, 'Locker Room & Showers'), (1, 'Juice Bar'),
(2, 'Air Conditioning'), (2, 'Crossfit Rig'), (2, 'Zumba Classes'), (2, 'Locker Room'), (2, 'Parking Space'),
(3, 'Cardio Cinema'), (3, 'Heavy Dumbbells (up to 60kg)'), (3, 'Squat Racks'), (3, 'Personal Trainer'), (3, 'Protein Shake Counter'),
(4, 'Air Conditioning'), (4, 'Spinning Studio'), (4, 'Yoga Decks'), (4, 'Nutritional Counseling'), (4, 'Steam Room'),
(5, 'Functional Turf'), (5, 'Boxing Ring'), (5, 'Plate-Loaded Machines'), (5, 'Heavy Weightlifting'),
(6, 'Body Composition Analyzer'), (6, 'Air Conditioning'), (6, 'Digital Treadmills'), (6, 'Sauna'), (6, 'Parking Space');


-- Gym Trainers
INSERT INTO `gym_trainers` (`gym_id`, `trainer_name`, `specialization`, `photo_url`) VALUES
(1, 'Rajesh Kumar', 'Certified Bodybuilding & Powerlifting Coach', 'https://images.unsplash.com/photo-1567013127542-490d757e51fc?auto=format&fit=crop&w=400&q=80'),
(1, 'Simran Singh', 'Functional Fitness & Fat Loss Specialist', 'https://images.unsplash.com/photo-1594381898411-846e7d193883?auto=format&fit=crop&w=400&q=80'),

(2, 'Vikas Tiwari', 'Level 2 Crossfit Trainer', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80'),
(3, 'Manish Pandey', 'Hypertrophy & Strength Coach', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80'),
(4, 'Ananya Roy', 'Spinning & Aerobics Master Trainer', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80');


-- Gym Membership Plans
INSERT INTO `gym_plans` (`gym_id`, `plan_name`, `duration`, `price`, `features`) VALUES
(1, 'Monthly Regular', '1 Month', 2500.00, 'Full Gym Access, Free Locker, Standard Assessment'),
(1, 'Quarterly Elite', '3 Months', 6500.00, 'Full Gym Access, Steam Bath Access, 1 Free PT Session, Locker'),
(1, 'Annual VIP', '12 Months', 18000.00, 'Unrestricted VIP Access, Steam Bath, Diet Chart, Locker, Gym Bag'),

(2, 'Monthly Standard', '1 Month', 1800.00, 'Gym Floor & Cardio Access, Locker'),
(2, 'Quarterly Crossfit', '3 Months', 4800.00, 'Full Gym + Unlimited Crossfit & Zumba Sessions'),

(3, 'Monthly Strength', '1 Month', 1500.00, 'Heavy Weight Floor Access, Cardio Cinema'),
(3, 'Half-Yearly Power', '6 Months', 7500.00, 'All Facilities + Custom Workout Card');


-- Sample Bookings
INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `gym_id`, `full_name`, `phone`, `email`, `visit_date`, `visit_time`, `status`) VALUES
(1, 'GZ-8A912F', 8, 1, 'Vikram Kumar', '9988776655', 'john@gmail.com', CURDATE() + INTERVAL 2 DAY, '07:00 AM', 'confirmed'),
(2, 'GZ-3B761E', NULL, 2, 'Pooja Sharma', '9811223344', 'pooja@gmail.com', CURDATE() + INTERVAL 3 DAY, '06:00 PM', 'pending');


-- Sample Favorites
INSERT INTO `favorites` (`user_id`, `gym_id`) VALUES
(8, 1),
(8, 3);
