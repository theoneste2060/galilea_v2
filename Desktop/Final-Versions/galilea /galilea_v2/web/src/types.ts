export interface Timestamped {
  created_at: string;
  updated_at: string;
}

export interface SiteSetting {
  id: number;
  key: string;
  label: string;
  value: string;
  group_name: string;
  updated_at: string;
}

export interface Service extends Timestamped {
  id: number;
  title: string;
  slug: string;
  short_description: string;
  description: string;
  icon: string;
  image_url: string | null;
  image_object_key: string | null;
  image_original_filename: string | null;
  featured: boolean;
  sort_order: number;
  is_active: boolean;
}

export interface NewsPost extends Timestamped {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  body: string;
  category: string;
  image_url: string | null;
  published: boolean;
  published_at: string;
}

export interface Testimonial extends Timestamped {
  id: number;
  client_name: string;
  company: string;
  quote: string;
  rating: number;
  is_active: boolean;
}

export interface TeamMember extends Timestamped {
  id: number;
  full_name: string;
  role: string;
  bio: string;
  image_url: string | null;
  sort_order: number;
  is_active: boolean;
}

export interface Inquiry extends Timestamped {
  id: number;
  full_name: string;
  email: string;
  phone: string | null;
  company: string | null;
  service_interest: string;
  message: string;
  status: string;
}

export interface DashboardBlock {
  type: string;
  content: string;
  metadata: Record<string, unknown>;
}

export interface DashboardPage {
  id: number;
  title: string;
  slug: string;
  icon: string;
  blocks: DashboardBlock[];
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface PublicSitePayload {
  settings: Record<string, string>;
  services: Service[];
  news: NewsPost[];
  testimonials: Testimonial[];
  team: TeamMember[];
  dashboard_pages: DashboardPage[];
}

export interface DashboardStats {
  services: number;
  news_posts: number;
  inquiries: number;
  new_inquiries: number;
  testimonials: number;
  shipments: number;
  newsletter_subscribers: number;
  admin_users: number;
}

export interface AdminLoginResponse {
  token: string;
}

export interface AdminLoginRequest {
  username: string;
  password: string;
}

export interface RuntimeUploadRequest {
  filename: string;
  content_type: string;
  category?: string;
}

export interface RuntimeUploadResponse {
  upload_url: string;
  method: "PUT";
  object_key: string;
  original_filename: string;
  public_url?: string | null;
  headers: Record<string, string>;
}

export interface ShipmentStage {
  label: string;
  description: string;
  completed: boolean;
  timestamp: string | null;
}

export interface ShipmentTrackingResponse {
  reference_number: string;
  customer_name: string;
  route: string;
  current_stage: string;
  status: string;
  stages: ShipmentStage[];
  updated_at: string;
}

export interface Shipment extends ShipmentTrackingResponse, Timestamped {
  id: number;
}

export interface NewsletterSubscriber extends Timestamped {
  id: number;
  email: string;
  full_name: string | null;
  source: string;
  is_active: boolean;
}

export interface NewsletterSubscriberCreate {
  email: string;
  full_name?: string;
  source?: string;
}

export interface AdminUser extends Timestamped {
  id: number;
  username: string;
  full_name: string;
  role: string;
  delegated_pages: string[];
  is_active: boolean;
}

export interface InquiryCreate {
  full_name: string;
  email: string;
  phone?: string;
  company?: string;
  service_interest: string;
  message: string;
}

export type AdminCollection = "services" | "news" | "testimonials" | "team" | "inquiries" | "pages" | "settings";
