import type {
  AdminCollection,
  AdminLoginRequest,
  AdminLoginResponse,
  AdminUser,
  DashboardPage,
  DashboardStats,
  Inquiry,
  InquiryCreate,
  NewsPost,
  NewsletterSubscriber,
  NewsletterSubscriberCreate,
  PublicSitePayload,
  RuntimeUploadRequest,
  RuntimeUploadResponse,
  Service,
  Shipment,
  ShipmentTrackingResponse,
  SiteSetting,
  TeamMember,
  Testimonial,
} from "@/types";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";
const ADMIN_TOKEN_KEY = "galilea_admin_token";

export function getAdminToken(): string {
  return sessionStorage.getItem(ADMIN_TOKEN_KEY) || "";
}

export function setAdminToken(token: string): void {
  sessionStorage.setItem(ADMIN_TOKEN_KEY, token);
}

export function clearAdminToken(): void {
  sessionStorage.removeItem(ADMIN_TOKEN_KEY);
}

async function apiFetch<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...options.headers,
    },
  });

  if (!response.ok) {
    let message = "Something went wrong. Please try again.";
    try {
      const body = (await response.json()) as { detail?: string };
      if (body.detail) {
        message = body.detail;
      }
    } catch {
      // Keep friendly fallback message.
    }
    throw new Error(message);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return response.json() as Promise<T>;
}

function adminFetch<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const token = getAdminToken();
  return apiFetch<T>(endpoint, {
    ...options,
    headers: {
      "X-Admin-Token": token,
      ...options.headers,
    },
  });
}

export const contentApi = {
  getPublicSite: () => apiFetch<PublicSitePayload>("/content"),
  createInquiry: (payload: InquiryCreate) =>
    apiFetch<Inquiry>("/content/inquiries", {
      method: "POST",
      body: JSON.stringify(payload),
    }),
  subscribeNewsletter: (payload: NewsletterSubscriberCreate) =>
    apiFetch<NewsletterSubscriber>("/content/newsletter", {
      method: "POST",
      body: JSON.stringify(payload),
    }),
};

export const shipmentApi = {
  track: (referenceNumber: string) =>
    apiFetch<ShipmentTrackingResponse>(`/shipments/track/${encodeURIComponent(referenceNumber.trim().toUpperCase())}`),
};

export const uploadApi = {
  presign: (payload: RuntimeUploadRequest) =>
    apiFetch<RuntimeUploadResponse>("/runtime-uploads/presign", {
      method: "POST",
      body: JSON.stringify(payload),
    }),
  uploadFile: async (contract: RuntimeUploadResponse, file: File) => {
    const response = await fetch(contract.upload_url, {
      method: contract.method,
      headers: contract.headers,
      body: file,
    });
    if (!response.ok) {
      throw new Error("Unable to upload the selected image. Please try again.");
    }
  },
};

export const adminApi = {
  login: (payload: AdminLoginRequest) =>
    apiFetch<AdminLoginResponse>("/admin/login", {
      method: "POST",
      body: JSON.stringify(payload),
    }),
  getStats: () => adminFetch<DashboardStats>("/admin/stats"),
  getSettings: () => adminFetch<SiteSetting[]>("/admin/settings"),
  getServices: () => adminFetch<Service[]>("/admin/services"),
  getNews: () => adminFetch<NewsPost[]>("/admin/news"),
  getTestimonials: () => adminFetch<Testimonial[]>("/admin/testimonials"),
  getTeam: () => adminFetch<TeamMember[]>("/admin/team"),
  getInquiries: () => adminFetch<Inquiry[]>("/admin/inquiries"),
  getPages: () => adminFetch<DashboardPage[]>("/admin/pages"),
  getShipments: () => adminFetch<Shipment[]>("/admin/shipments"),
  getNewsletter: () => adminFetch<NewsletterSubscriber[]>("/admin/newsletter"),
  getUsers: () => adminFetch<AdminUser[]>("/admin/users"),
  delete: (collection: "shipments" | "newsletter" | "users", id: number) =>
    adminFetch<void>(`/admin/${collection}/${id}`, { method: "DELETE" }),
  update: <T>(collection: AdminCollection, id: number, payload: Record<string, unknown>) =>
    adminFetch<T>(`/admin/${collection}/${id}`, {
      method: "PUT",
      body: JSON.stringify(payload),
    }),
  create: <T>(collection: AdminCollection, payload: Record<string, unknown>) =>
    adminFetch<T>(`/admin/${collection}`, {
      method: "POST",
      body: JSON.stringify(payload),
    }),
};
