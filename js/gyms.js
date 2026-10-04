/* ==========================================================================
   GYMZONE - Gym Data & Storage Service (Jamshedpur Seed Data)
   ========================================================================== */

const INITIAL_GYMS = [
  {
    id: "gym_1",
    name: "Pulse Fitness Studio",
    location: "Bistupur",
    city: "Jamshedpur",
    address: "Regal Plaza Building, Main Road, Bistupur, Jamshedpur, Jharkhand 831001",
    price: 2500,
    plans: {
      monthly: 2500,
      quarterly: 6800,
      halfYearly: 12500,
      yearly: 22000
    },
    rating: 4.8,
    reviewCount: 48,
    image: "https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["AC", "Personal Trainer", "Steam Bath", "Cardio", "Crossfit", "Parking", "Locker Room"],
    timings: "5:30 AM - 10:00 PM",
    openingDays: "Mon - Sat (Sun: 6:00 AM - 1:00 PM)",
    phone: "+91 98351 12345",
    email: "info@pulsefitness.com",
    status: "approved",
    featured: true,
    isNew: false,
    ownerId: "owner_1",
    description: "Pulse Fitness Studio is Jamshedpur's premier fitness destination in Bistupur. Featuring imported strength machinery, dedicated Crossfit zone, and certified coaches to help you reach your peak physical performance.",
    trainers: [
      { name: "Vikram Sen", role: "Head Strength & Conditioning Coach", img: "https://images.unsplash.com/photo-1567013127542-490d757e51fc?q=80&w=300&auto=format&fit=crop" },
      { name: "Ananya Roy", role: "Certified Pilates & Crossfit Specialist", img: "https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_1", userName: "Aman Verma", rating: 5, date: "2026-09-20", comment: "Outstanding equipment and very hygienic environment. Coach Vikram is top tier!" },
      { id: "rev_2", userName: "Neha Gupta", rating: 4, date: "2026-09-15", comment: "Great location in Bistupur with spacious parking and awesome steam bath facility." }
    ]
  },
  {
    id: "gym_2",
    name: "Iron Vault Gym",
    location: "Sakchi",
    city: "Jamshedpur",
    address: "3rd Floor, SNP Area, Near Ambagan, Sakchi, Jamshedpur, Jharkhand 831001",
    price: 1800,
    plans: {
      monthly: 1800,
      quarterly: 5000,
      halfYearly: 9200,
      yearly: 16500
    },
    rating: 4.6,
    reviewCount: 35,
    image: "https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["Weightlifting", "Functional Training", "Locker Room", "Supplements Counter", "AC"],
    timings: "6:00 AM - 9:30 PM",
    openingDays: "Mon - Sat",
    phone: "+91 94311 87654",
    email: "contact@ironvaultgym.com",
    status: "approved",
    featured: true,
    isNew: false,
    ownerId: "owner_1",
    description: "Iron Vault Gym in Sakchi offers a hardcore powerlifting and bodybuilding setup. Equipped with heavy dumbells up to 60kg, squat racks, and specialized benching platforms.",
    trainers: [
      { name: "Rajesh Kumar", role: "Bodybuilding Specialist", img: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_3", userName: "Rohan Das", rating: 5, date: "2026-09-18", comment: "Best powerlifting environment in Jamshedpur! Reasonable monthly fee." }
    ]
  },
  {
    id: "gym_3",
    name: "Gold Standard Fitness Club",
    location: "Kadma",
    city: "Jamshedpur",
    address: "Bhatia Park Link Road, Kadma, Jamshedpur, Jharkhand 831005",
    price: 3200,
    plans: {
      monthly: 3200,
      quarterly: 8900,
      halfYearly: 16000,
      yearly: 28000
    },
    rating: 4.9,
    reviewCount: 64,
    image: "https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["Swimming Pool", "AC", "Personal Trainer", "Yoga", "Zumba", "Cafe", "Steam Bath", "Sauna"],
    timings: "5:00 AM - 10:30 PM",
    openingDays: "Mon - Sun",
    phone: "+91 99343 55443",
    email: "support@goldstandardfit.com",
    status: "approved",
    featured: true,
    isNew: false,
    ownerId: "owner_2",
    description: "Gold Standard Fitness Club is a luxury wellness sanctuary in Kadma featuring a temperature-controlled swimming pool, full spectrum cardio floor, and organic protein smoothie bar.",
    trainers: [
      { name: "Suresh Mukherjee", role: "Senior Fitness Director", img: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300&auto=format&fit=crop" },
      { name: "Pooja Banerjee", role: "Yoga Master & Zumba Lead", img: "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_4", userName: "Priyanka Sharma", rating: 5, date: "2026-09-22", comment: "The pool and Zumba classes are absolute bliss after work!" }
    ]
  },
  {
    id: "gym_4",
    name: "Alpha Elite Fitness Arena",
    location: "CH Area",
    city: "Jamshedpur",
    address: "Road No. 2, Circuit House Area, Jamshedpur, Jharkhand 831001",
    price: 4000,
    plans: {
      monthly: 4000,
      quarterly: 11000,
      halfYearly: 20000,
      yearly: 35000
    },
    rating: 5.0,
    reviewCount: 18,
    image: "https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["Swimming Pool", "Personal Coach", "AC", "Luxury Spa", "Bio-Metrics", "Cafe", "Valet Parking"],
    timings: "5:00 AM - 11:00 PM",
    openingDays: "Mon - Sun",
    phone: "+91 97714 99887",
    email: "vip@alphaelite.in",
    status: "approved",
    featured: true,
    isNew: true,
    ownerId: "owner_2",
    description: "Newly opened ultra-premium club in CH Area with AI bio-metric body tracking, elite sports rehab center, luxury spa, and private 1-on-1 personal training suites.",
    trainers: [
      { name: "Captain Devansh Mehta", role: "High Performance Coach", img: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_5", userName: "Dr. K. N. Tata", rating: 5, date: "2026-09-28", comment: "World-class facilities right in CH Area Jamshedpur." }
    ]
  },
  {
    id: "gym_5",
    name: "Titanium Crossfit Studio",
    location: "Sonari",
    city: "Jamshedpur",
    address: "Kagaznagar Main Road, Sonari, Jamshedpur, Jharkhand 831011",
    price: 2200,
    plans: {
      monthly: 2200,
      quarterly: 6000,
      halfYearly: 11000,
      yearly: 19500
    },
    rating: 4.7,
    reviewCount: 29,
    image: "https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=800&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["Crossfit", "Calisthenics Zone", "TRX", "Locker Room", "AC", "Personal Trainer"],
    timings: "6:00 AM - 10:00 PM",
    openingDays: "Mon - Sat",
    phone: "+91 91223 44556",
    email: "sonari@titaniumfit.com",
    status: "approved",
    featured: false,
    isNew: true,
    ownerId: "owner_1",
    description: "Sonari's first dedicated high-intensity functional fitness box. Specialist coaches in calisthenics, Olympic weightlifting, and obstacle course conditioning.",
    trainers: [
      { name: "Karan Singh", role: "Crossfit Level-2 Trainer", img: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_6", userName: "Simran Kaur", rating: 5, date: "2026-09-25", comment: "Incredible energy! The group crossfit workouts push you to another level." }
    ]
  },
  {
    id: "gym_6",
    name: "Body Craft Health Club",
    location: "Telco",
    city: "Jamshedpur",
    address: "Plaza Market, Near Telco Club, Telco, Jamshedpur, Jharkhand 831004",
    price: 1500,
    plans: {
      monthly: 1500,
      quarterly: 4200,
      halfYearly: 7800,
      yearly: 14000
    },
    rating: 4.5,
    reviewCount: 31,
    image: "https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["Weightlifting", "Cardio", "Juice Bar", "Parking", "Locker Room"],
    timings: "6:00 AM - 9:00 PM",
    openingDays: "Mon - Sat",
    phone: "+91 93345 66778",
    email: "telco@bodycraft.com",
    status: "approved",
    featured: false,
    isNew: false,
    ownerId: "owner_1",
    description: "Spacious multi-station health club located in Telco colony with comprehensive cardio and weight machinery suitable for beginners and athletes alike.",
    trainers: [
      { name: "Manoj Mahato", role: "General Fitness Instructor", img: "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_7", userName: "Sumit Dey", rating: 4, date: "2026-09-10", comment: "Friendly atmosphere and very budget-friendly monthly plans." }
    ]
  },
  {
    id: "gym_7",
    name: "Flex Zone Power Arena",
    location: "Adityapur",
    city: "Jamshedpur",
    address: "Tata-Kandra Road, Near All India Radio, Adityapur, Jamshedpur, Jharkhand 831013",
    price: 2000,
    plans: {
      monthly: 2000,
      quarterly: 5500,
      halfYearly: 10000,
      yearly: 18000
    },
    rating: 4.7,
    reviewCount: 40,
    image: "https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["AC", "Powerlifting Platforms", "Steam Bath", "Female Trainer", "Personal Trainer"],
    timings: "5:30 AM - 10:00 PM",
    openingDays: "Mon - Sat",
    phone: "+91 98353 77112",
    email: "adityapur@flexzone.com",
    status: "approved",
    featured: false,
    isNew: false,
    ownerId: "owner_2",
    description: "Modern facility on Tata-Kandra Road with specialized powerlifting deadlift platforms, steam rooms, and female certified trainers.",
    trainers: [
      { name: "Sneha Soren", role: "Women's Fitness & Strength Coach", img: "https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_8", userName: "Tanvi Hansda", rating: 5, date: "2026-09-14", comment: "Extremely comfortable for women workouts with Trainer Sneha!" }
    ]
  },
  {
    id: "gym_8",
    name: "Matrix Gym & Wellness",
    location: "Mango",
    city: "Jamshedpur",
    address: "Dimna Road, Near Blue Bells School, Mango, Jamshedpur, Jharkhand 831012",
    price: 1200,
    plans: {
      monthly: 1200,
      quarterly: 3300,
      halfYearly: 6200,
      yearly: 11000
    },
    rating: 4.4,
    reviewCount: 22,
    image: "https://images.unsplash.com/photo-1593079831268-3381b0db4a77?q=80&w=800&auto=format&fit=crop",
    gallery: [
      "https://images.unsplash.com/photo-1593079831268-3381b0db4a77?q=80&w=800&auto=format&fit=crop"
    ],
    facilities: ["Strength Equipment", "Cardio", "Locker Room", "Parking"],
    timings: "6:00 AM - 9:30 PM",
    openingDays: "Mon - Sat",
    phone: "+91 94313 00998",
    email: "dimna@matrixgym.com",
    status: "approved",
    featured: false,
    isNew: false,
    ownerId: "owner_1",
    description: "Affordable and clean gym for student workouts on Dimna Road. Equipped with essentials for weight gain, fat loss, and general conditioning.",
    trainers: [
      { name: "Suraj Pandey", role: "Cardio & Weight Coach", img: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300&auto=format&fit=crop" }
    ],
    reviews: [
      { id: "rev_9", userName: "Ravi Shankar", rating: 4, date: "2026-08-30", comment: "Super affordable for college students living in Mango area." }
    ]
  }
];

// Initialize localStorage Seed Data
function getGymsFromStorage() {
  const data = localStorage.getItem("gymzone_gyms");
  if (!data) {
    localStorage.setItem("gymzone_gyms", JSON.stringify(INITIAL_GYMS));
    return INITIAL_GYMS;
  }
  try {
    return JSON.parse(data);
  } catch (e) {
    localStorage.setItem("gymzone_gyms", JSON.stringify(INITIAL_GYMS));
    return INITIAL_GYMS;
  }
}

function saveGymsToStorage(gyms) {
  localStorage.setItem("gymzone_gyms", JSON.stringify(gyms));
}

function getGymById(id) {
  const gyms = getGymsFromStorage();
  return gyms.find(g => g.id === id);
}

function addGymListing(newGym) {
  const gyms = getGymsFromStorage();
  gyms.push(newGym);
  saveGymsToStorage(gyms);
  return newGym;
}

function updateGymListing(updatedGym) {
  let gyms = getGymsFromStorage();
  const index = gyms.findIndex(g => g.id === updatedGym.id);
  if (index !== -1) {
    gyms[index] = updatedGym;
    saveGymsToStorage(gyms);
    return true;
  }
  return false;
}

function deleteGymListing(gymId) {
  let gyms = getGymsFromStorage();
  gyms = gyms.filter(g => g.id !== gymId);
  saveGymsToStorage(gyms);
}

// Auto-seed on initial load
getGymsFromStorage();
