# GYMZONE - Local Gym Discovery & Free Trial Booking Platform

**GYMZONE** is a full-featured, modern, responsive gym discovery website built with HTML5, CSS3, JavaScript (ES6+), PHP, and MySQL. It empowers users to discover fitness centers in their city (featuring sample data for Jamshedpur), explore high-resolution photos, review facilities and equipment, check membership prices, and book 1-day free trial passes online.

---

## 🌟 Features Overview

### 1. 🏠 Homepage (`index.php`)
- Premium black and vibrant orange fitness theme.
- Large background hero banner with call-to-action overlay.
- Search bar to search gyms by location/area or keyword.
- Quick navigation buttons: *Explore Gyms*, *New Gyms*, *Book a Trial*.
- Showcase of **Featured Gyms** & **Recently Opened Gyms**.
- Gym Owner partnership call-to-action.

### 2. 🔍 Explore Gyms & Filters (`explore.php`)
- Gym cards showing photos, area tag, monthly fees, badges (`NEW`), facilities list, and quick action buttons.
- Dynamic search & filters:
  - Search by Gym name or keyword.
  - Filter by Jamshedpur location area (*Bistupur, Sakchi, Telco, Sonari, Kadma, Adityapur*).
  - Filter by Price Range (*Under ₹1,500*, *₹1,500 - ₹2,000*, *Above ₹2,000*).
  - Filter by Facilities (*Air Conditioning, Crossfit, Steam Bath, Personal Trainers, Sauna, Locker Room*).
  - Filter for Newly Opened Gyms.

### 3. 🏋️ Gym Details Page (`gym-details.php`)
- High-definition gallery with interactive thumbnail switcher.
- Full street address and operating hours (*Opening & Closing times*).
- Interactive facilities & equipment checklist.
- Certified trainer profiles (with photos & specializations).
- Membership plans & pricing cards (*Monthly, Quarterly, Annual*).
- Direct **Free Trial Booking Form**.

### 4. 🎟️ Free Trial Booking (`book-trial.php`)
- Free trial pass creation form (*Full Name, Mobile Number, Email, Visit Date, Visit Time*).
- Instant booking code generation (e.g. `GZ-9X82F1`).
- Saves booking details into MySQL database with status `pending`.
- Printable/viewable booking confirmation pass screen.

### 5. 🔐 Authentication & Accounts (`login.php`, `register.php`, `logout.php`)
- Secure registration & login system with password hashing (`password_hash` with `PASSWORD_DEFAULT`).
- Multi-role support: **Fitness Member (User)**, **Gym Owner**, and **System Admin**.
- Instant demo account autofill buttons on the login page.

### 6. 👤 Fitness Member Dashboard (`user-dashboard.php`)
- User profile summary.
- Saved favorite gyms grid (with AJAX toggle favorite support in `toggle-favorite.php`).
- Trial booking history table with live status tracking (*Pending*, *Confirmed*, *Cancelled*).

### 7. 🏪 Gym Owner Dashboard (`owner-dashboard.php`, `add-gym.php`, `edit-gym.php`, `delete-gym.php`)
- Owner statistics dashboard (*Listed Gyms, Total Bookings*).
- Add new gym listings with photo URLs, facilities, location, description, and operating hours.
- Edit and delete owned gym listings.
- Manage free trial visitor bookings (*Confirm* or *Cancel* booking requests).

### 8. 🛡️ Admin Dashboard (`admin-dashboard.php`, `approve-gym.php`)
- Secure admin panel with system counters (*Members, Owners, Gyms, Bookings*).
- Approve or Reject pending gym listings.
- View and manage all registered user accounts and gym owners.
- Monitor all trial bookings across all gyms.

---

## 💻 XAMPP Setup & Installation Instructions

Follow these simple steps to run **GYMZONE** locally using XAMPP:

### Step 1: Copy Project Files to XAMPP `htdocs`
1. Open XAMPP directory on your PC (usually `C:\xampp\htdocs\`).
2. Copy the entire `gymzone` folder into `C:\xampp\htdocs\gymzone`.

### Step 2: Start XAMPP Services
1. Launch **XAMPP Control Panel**.
2. Click **Start** for both **Apache** and **MySQL**.

### Step 3: Create Database & Import SQL
1. Open your browser and navigate to **[http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)**.
2. Click **New** on the left menu, enter database name `gymzone`, and click **Create**.
3. Select the `gymzone` database, go to the **Import** tab.
4. Choose the file `gymzone.sql` located inside the `gymzone` project directory and click **Import** (or **Go**).

### Step 4: Open GYMZONE in Browser
Open your browser and visit:
👉 **[http://localhost/gymzone/](http://localhost/gymzone/)**

---

## 🔑 Demo Account Credentials

All accounts use demo data clearly marked for testing.

| Account Type | Email | Password | Dashboard Link |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@gymzone.com` | `Admin@123` | `http://localhost/gymzone/admin-dashboard.php` |
| **Gym Owner** | `owner1@titanium.com` | `Owner@123` | `http://localhost/gymzone/owner-dashboard.php` |
| **Fitness Member** | `john@gmail.com` | `User@123` | `http://localhost/gymzone/user-dashboard.php` |

*(Note: On the `login.php` page, you can also click the demo buttons to automatically populate the login fields!)*

---

## 📂 Project Directory Structure

```
gymzone/
├── config/
│   └── db.php                     # PDO Database connection
├── includes/
│   ├── header.php                 # Responsive header & navbar
│   ├── footer.php                 # Footer template
│   └── functions.php              # Utility functions & helpers
├── assets/
│   ├── css/
│   │   └── style.css              # Theme CSS styling & responsive breakpoints
│   └── js/
│       └── main.js                # AJAX favorite toggle, gallery switcher, mobile menu
├── index.php                      # Homepage (Featured & New Gyms)
├── explore.php                    # Gym discovery & multi-filter search
├── gym-details.php                # Detailed gym view, gallery, equipment, trial form
├── book-trial.php                 # Free trial booking handler & confirmation
├── toggle-favorite.php            # AJAX favorite toggle API
├── login.php                      # Multi-role user login page
├── register.php                   # Account registration page
├── logout.php                     # Session cleanup script
├── user-dashboard.php             # Member dashboard (Bookings & Saved Gyms)
├── owner-dashboard.php            # Gym Owner dashboard
├── add-gym.php                    # Add new gym listing form
├── edit-gym.php                   # Edit gym listing form
├── delete-gym.php                 # Delete gym listing script
├── update-booking-status.php      # Booking status update handler
├── admin-dashboard.php            # Admin control panel
├── approve-gym.php                # Admin gym approval/rejection handler
├── gymzone.sql                    # SQL Database schema & sample Jamshedpur demo data
└── README.md                      # Documentation & XAMPP guide
```

---

## 📝 Demo Notice
*All gym listings, photos, and free trial passes provided in this project are for demonstration purposes in Jamshedpur.*
