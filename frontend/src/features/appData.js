export const eventTypeOptions = [
  { value: 'all', label: 'All Event Types' },
  { value: 'wedding', label: 'Wedding' },
  { value: 'monk_ceremony', label: 'Monk Ceremony' },
  { value: 'house_blessing', label: 'House Blessing' },
  { value: 'company_party', label: 'Company Party' },
  { value: 'birthday', label: 'Birthday' },
  { value: 'school_event', label: 'School Event' },
  { value: 'engagement', label: 'Engagement' },
  { value: 'anniversary', label: 'Anniversary' },
  { value: 'baby_shower', label: 'Baby Shower' },
  { value: 'graduation', label: 'Graduation' },
  { value: 'festival', label: 'Festival' },
  { value: 'other', label: 'Other' },
]

export const eventTypeMap = Object.fromEntries(eventTypeOptions.map((item) => [item.value, item.label]))

export const vendorProfile = {
  name: 'Luxe Bloom Florals',
  subtitle: 'Premium Wedding & Event Floral Design',
  rating: '4.9 (142 reviews)',
}

export const stats = [
  { label: 'Years Exp.', value: '15+' },
  { label: 'Events Done', value: '1.2k' },
  { label: 'Eco-Friendly', value: '100%' },
  { label: 'Staff', value: '12' },
]

export const reviews = [
  {
    name: 'Emma Wilson',
    text: 'Absolutely stunning floral designs for our wedding. Everyone kept asking who did them.',
  },
  {
    name: 'James Kovac',
    text: 'Professional and creative. They made our corporate gala look world-class.',
  },
]

export const fallbackVendorLocation = '88 Flower District, New York, NY 10001'
export const serviceFeeRate = 0.1

export const matchingServicesCatalog = [
  { id: 'mc-host', name: 'Achar MC', description: 'Traditional ceremony host and flow management.', price: 250, eventTypes: ['wedding', 'engagement', 'anniversary'] },
  { id: 'monk-set', name: 'Monk Ceremony Set', description: 'Complete ritual setup for monk blessing.', price: 320, eventTypes: ['monk_ceremony', 'house_blessing'] },
  { id: 'sound-light', name: 'Sound & Lighting', description: 'PA system, DJ support, and stage lighting.', price: 450, eventTypes: ['company_party', 'birthday', 'school_event', 'festival', 'graduation'] },
  { id: 'photo-video', name: 'Photo & Video', description: 'Event photography and highlight video coverage.', price: 850, eventTypes: ['wedding', 'engagement', 'birthday', 'company_party', 'school_event', 'graduation', 'anniversary'] },
  { id: 'premium-catering', name: 'Premium Catering', description: 'Buffet and service crew package.', price: 1500, eventTypes: ['wedding', 'company_party', 'birthday', 'festival', 'anniversary'] },
  { id: 'decor-pack', name: 'Decoration Package', description: 'Theme design, floral setup, and backdrops.', price: 1200, eventTypes: ['wedding', 'engagement', 'baby_shower', 'birthday', 'school_event'] },
  { id: 'cake-dessert', name: 'Cake & Dessert Bar', description: 'Customized cake and dessert station.', price: 380, eventTypes: ['birthday', 'anniversary', 'baby_shower', 'graduation'] },
  { id: 'cultural-band', name: 'Khmer Cultural Band', description: 'Live traditional music performance.', price: 600, eventTypes: ['wedding', 'festival', 'engagement', 'other'] },
]

export const packageCatalogByEventType = {
  wedding: [
    { id: 'wed-royal', title: 'Royal Wedding Signature', description: 'Elegant ceremony + reception package.', basePrice: 2800 },
    { id: 'wed-classic', title: 'Classic Wedding Essentials', description: 'Balanced package for medium-size weddings.', basePrice: 1900 },
  ],
  monk_ceremony: [
    { id: 'monk-blessing', title: 'Monk Blessing Complete', description: 'Full monk ceremony setup and coordination.', basePrice: 1250 },
    { id: 'monk-traditional', title: 'Traditional Blessing Basic', description: 'Core ritual set with support team.', basePrice: 900 },
  ],
  house_blessing: [
    { id: 'house-premium', title: 'House Blessing Premium', description: 'Blessing flow, altar setup, and host support.', basePrice: 1100 },
    { id: 'house-basic', title: 'House Blessing Basic', description: 'Essential ceremonial package for new homes.', basePrice: 780 },
  ],
  company_party: [
    { id: 'corp-gala', title: 'Corporate Gala Package', description: 'Production-ready package for company celebrations.', basePrice: 3200 },
    { id: 'corp-social', title: 'Corporate Social Night', description: 'Smart setup for networking and social events.', basePrice: 2100 },
  ],
  birthday: [
    { id: 'bday-deluxe', title: 'Birthday Deluxe Party', description: 'Fun and vibrant premium birthday setup.', basePrice: 1500 },
    { id: 'bday-family', title: 'Birthday Family Package', description: 'Budget-friendly birthday arrangement.', basePrice: 980 },
  ],
  school_event: [
    { id: 'school-annual', title: 'School Annual Event', description: 'Stage, sound, and logistics for school functions.', basePrice: 2600 },
    { id: 'school-ceremony', title: 'School Ceremony Package', description: 'Graduation or ceremony focused setup.', basePrice: 1800 },
  ],
  engagement: [
    { id: 'eng-gold', title: 'Engagement Gold Package', description: 'Elegant engagement decor and hosting support.', basePrice: 1700 },
    { id: 'eng-intimate', title: 'Engagement Intimate Package', description: 'Compact setup for close-family events.', basePrice: 1200 },
  ],
  anniversary: [
    { id: 'anni-romance', title: 'Anniversary Romance Package', description: 'Decor and ambiance tailored for anniversaries.', basePrice: 1400 },
    { id: 'anni-family', title: 'Anniversary Family Package', description: 'Warm and simple anniversary celebration setup.', basePrice: 1050 },
  ],
  baby_shower: [
    { id: 'baby-soft', title: 'Baby Shower Soft Theme', description: 'Themed decor and coordinated setup.', basePrice: 1300 },
    { id: 'baby-classic', title: 'Baby Shower Classic', description: 'Essential baby shower arrangement package.', basePrice: 920 },
  ],
  graduation: [
    { id: 'grad-stage', title: 'Graduation Stage Package', description: 'Photo zone, stage setup, and sound support.', basePrice: 2100 },
    { id: 'grad-party', title: 'Graduation Party Package', description: 'Post-ceremony celebration package.', basePrice: 1450 },
  ],
  festival: [
    { id: 'fest-premium', title: 'Festival Premium Package', description: 'Large-scale festival production support.', basePrice: 4200 },
    { id: 'fest-community', title: 'Community Festival Package', description: 'Reliable setup for community events.', basePrice: 2600 },
  ],
  other: [
    { id: 'other-custom', title: 'Custom Event Package', description: 'Flexible package for unique requirements.', basePrice: 1600 },
    { id: 'other-essentials', title: 'Event Essentials', description: 'Core setup for miscellaneous events.', basePrice: 1100 },
  ],
}

export const packageImageByEventType = {
  wedding:
    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80',
  monk_ceremony:
    'https://images.unsplash.com/photo-1529699211952-734e80c4d42b?auto=format&fit=crop&w=900&q=80',
  house_blessing:
    'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=900&q=80',
  company_party:
    'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=80',
  birthday:
    'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=900&q=80',
  school_event:
    'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=900&q=80',
  engagement:
    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=900&q=80',
  anniversary:
    'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
  baby_shower:
    'https://images.unsplash.com/photo-1478144592103-25e218a04891?auto=format&fit=crop&w=900&q=80',
  graduation:
    'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=900&q=80',
  festival:
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
  other:
    'https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=900&q=80',
}

const packageServiceTemplatesByEventType = {
  wedding: [
    { name: 'Ceremony Styling', detail: 'Aisle decor, floral focal points, and coordinated welcome area setup.' },
    { name: 'Reception Decor', detail: 'Head table styling, centerpiece arrangement, and backdrop dressing.' },
    { name: 'Guest Flow Support', detail: 'Timeline guidance for seating, grand entry, and reception transitions.' },
  ],
  monk_ceremony: [
    { name: 'Ritual Setup', detail: 'Prepared altar area, monk seating, and ceremonial layout support.' },
    { name: 'Offerings Coordination', detail: 'Arrangement guidance for offerings and family participation sequence.' },
    { name: 'Ceremony Timing', detail: 'On-site support to keep the blessing flow smooth and respectful.' },
  ],
  house_blessing: [
    { name: 'Home Altar Arrangement', detail: 'Main blessing corner styled with practical ceremonial essentials.' },
    { name: 'Family Guidance', detail: 'Clear run-of-show for host family and invited relatives.' },
    { name: 'Cleanup & Reset', detail: 'Light post-ceremony reset to restore the space quickly.' },
  ],
  company_party: [
    { name: 'Stage & Layout Plan', detail: 'Event floor mapping for seating, stage, and circulation paths.' },
    { name: 'Audio Visual Support', detail: 'Mic, speaker, and show-flow coordination with your event team.' },
    { name: 'Brand Styling', detail: 'Visual elements aligned to your company event identity.' },
  ],
  birthday: [
    { name: 'Theme Decor', detail: 'Color-matched backdrop, table styling, and photo corner setup.' },
    { name: 'Program Support', detail: 'Birthday agenda support for cake moment and activity timing.' },
    { name: 'Guest Comfort Setup', detail: 'Seating, flow, and presentation arranged for all age groups.' },
  ],
  school_event: [
    { name: 'Event Logistics', detail: 'Structured area zoning for students, staff, and guest movement.' },
    { name: 'Stage Management', detail: 'Cue support for speeches, performances, and recognition segments.' },
    { name: 'Safety-first Layout', detail: 'Clear traffic setup to maintain order during peak attendance.' },
  ],
  engagement: [
    { name: 'Ceremony Scene Design', detail: 'Elegant setup for ring exchange and family photo moments.' },
    { name: 'Guest Welcome Styling', detail: 'Entrance decor and hospitality table arrangement support.' },
    { name: 'Timeline Coordination', detail: 'Assistance for smooth transition between rituals and reception.' },
  ],
  anniversary: [
    { name: 'Memory-focused Decor', detail: 'Romantic setup with personalized visual storytelling elements.' },
    { name: 'Dinner Ambience', detail: 'Table styling and lighting mood planning for evening comfort.' },
    { name: 'Celebration Flow', detail: 'Support for speeches, toast moments, and media presentation.' },
  ],
  baby_shower: [
    { name: 'Soft Theme Styling', detail: 'Pastel-forward decor with coordinated photo-friendly corners.' },
    { name: 'Gift & Activity Station', detail: 'Practical arrangement for gifts, games, and family interactions.' },
    { name: 'Guest Seating Plan', detail: 'Comfortable layout optimized for relatives and close friends.' },
  ],
  graduation: [
    { name: 'Recognition Stage Setup', detail: 'Background, podium area, and graduate photo capture zone.' },
    { name: 'Program Sequence Support', detail: 'Agenda coordination for speeches and certificate segments.' },
    { name: 'Photo Session Planning', detail: 'Quick-flow arrangement to reduce waiting during photo rounds.' },
  ],
  festival: [
    { name: 'Large Area Zoning', detail: 'Segmented setup for booths, stage, and visitor circulation.' },
    { name: 'Performance Support', detail: 'Production-ready coordination for performers and technical teams.' },
    { name: 'Crowd Flow Management', detail: 'Entry/exit route planning and hotspot balancing guidance.' },
  ],
  other: [
    { name: 'Consultation & Scoping', detail: 'Requirements review and adaptable plan based on your event goal.' },
    { name: 'Core Setup Package', detail: 'Essential decor, layout, and sequence support for custom events.' },
    { name: 'On-site Coordination', detail: 'Operational support to keep key moments on schedule.' },
  ],
}

export function buildPackageServiceDescriptions(eventType, title) {
  const baseServices = packageServiceTemplatesByEventType[eventType] || packageServiceTemplatesByEventType.other
  const normalizedTitle = title.toLowerCase()
  const premiumTier = /(royal|signature|premium|gold|deluxe|gala|annual|romance|stage|custom)/.test(normalizedTitle)
  const essentialTier = /(classic|basic|family|intimate|community|essentials)/.test(normalizedTitle)

  let levelDetail = 'Balanced service coverage for standard event requirements.'
  if (premiumTier) levelDetail = 'Premium-level coordination with extended setup depth and enhanced finishing touches.'
  if (essentialTier) levelDetail = 'Essential-focused coverage optimized for practical budgets and compact timelines.'

  return [...baseServices, { name: 'Coordination Level', detail: levelDetail }]
}

export const conversationsSeed = [
  {
    id: 'luxe-bloom',
    name: 'Luxe Bloom Florals',
    preview: 'The peonies for the centerpiece will be ready by Thursday.',
    time: 'Just now',
    online: true,
    image:
      'https://images.unsplash.com/photo-1477511801984-4ad318ed9846?auto=format&fit=crop&w=180&q=80',
    messages: [
      {
        id: 1,
        from: 'them',
        text: 'Hi Sarah! I have received the shipment of Dutch peonies for your wedding. They are stunning.',
        time: '10:24 AM',
      },
      {
        id: 2,
        from: 'me',
        text: 'That is wonderful news! Do you have a photo of the color? I want to make sure it matches.',
        time: '10:26 AM',
      },
      {
        id: 3,
        from: 'them',
        text: 'Of course! Here is a quick snap from the workshop now.',
        image:
          'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=1400&q=80',
        time: '10:30 AM',
      },
      {
        id: 4,
        from: 'me',
        text: 'Perfect! They look exactly like the moodboard. The peonies for the centerpiece will be ready for final mockup Thursday then?',
        time: 'Just now',
      },
    ],
  },
  {
    id: 'grand-estates',
    name: 'Grand Estates Venue',
    preview: 'I have sent the updated floor plan for the gala.',
    time: '2h ago',
    online: false,
    image:
      'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=180&q=80',
    messages: [
      { id: 1, from: 'them', text: 'I have sent the updated floor plan for the gala.', time: '2h ago' },
    ],
  },
]
