import { packageImageByEventType, vendorProfile } from "./appData";

export const fallbackPublicEvents = [
  {
    id: 9001,
    title: "Signature Wedding Floral & Styling",
    description:
      "Full-venue floral design, aisle and backdrop styling tailored for intimate to mid-size weddings.",
    price: 1800,
    event_type: "wedding",
    service_mode: "package",
    location: "Phnom Penh, Cambodia",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.wedding,
    vendor: { name: vendorProfile.name },
    is_demo: true,
    is_active: true,
  },
  {
    id: 9002,
    title: "Monk Ceremony Setup",
    description:
      "Respectful altar layout, seating, sound, and offering coordination with flexible timing.",
    price: 650,
    event_type: "monk_ceremony",
    service_mode: "package",
    location: "Kandal Province",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.monk_ceremony,
    vendor: { name: vendorProfile.name },
    is_demo: true,
    is_active: true,
  },
  {
    id: 9003,
    title: "Corporate Gala Decor Coordination",
    description:
      "Stage styling, centerpiece planning, and guest flow support for polished company events.",
    price: 2400,
    event_type: "company_party",
    service_mode: "overall",
    location: "Phnom Penh, Cambodia",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.company_party,
    vendor: { name: vendorProfile.name },
    is_demo: true,
    is_active: true,
  },
  {
    id: 9004,
    title: "Birthday Backdrop and Balloon Bar",
    description:
      "Photo-ready backdrop, themed props, and balloon styling for kids or adult celebrations.",
    price: 520,
    event_type: "birthday",
    service_mode: "overall",
    location: "Siem Reap, Cambodia",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.birthday,
    vendor: { name: vendorProfile.name },
    is_demo: true,
    is_active: true,
  },
];
