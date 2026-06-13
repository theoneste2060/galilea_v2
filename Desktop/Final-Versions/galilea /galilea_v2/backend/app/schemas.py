from datetime import datetime
from typing import Any

from pydantic import BaseModel, ConfigDict, Field, model_validator


class BaseSchema(BaseModel):
    """Base schema with common Pydantic configuration"""

    model_config = ConfigDict(from_attributes=True, str_strip_whitespace=True)

    @model_validator(mode="before")
    @classmethod
    def reject_null_bytes(cls, data: Any) -> Any:
        """Reject null bytes in string values to prevent database errors."""
        if isinstance(data, dict):
            for key, value in data.items():
                if isinstance(value, str) and "\x00" in value:
                    raise ValueError(
                        f"Null bytes are not allowed in field '{key}'"
                    )
        return data


class TimestampMixin(BaseModel):
    """Mixin for entities with created_at/updated_at fields"""

    created_at: datetime
    updated_at: datetime


class SiteSettingBase(BaseSchema):
    key: str = Field(min_length=2, max_length=100)
    label: str = Field(min_length=2, max_length=160)
    value: str = Field(min_length=1)
    group_name: str = Field(default="general", min_length=2, max_length=80)


class SiteSettingCreate(SiteSettingBase):
    pass


class SiteSettingUpdate(BaseSchema):
    label: str | None = Field(default=None, min_length=2, max_length=160)
    value: str | None = Field(default=None, min_length=1)
    group_name: str | None = Field(default=None, min_length=2, max_length=80)


class SiteSetting(SiteSettingBase):
    id: int
    updated_at: datetime


class ServiceBase(BaseSchema):
    title: str = Field(min_length=2, max_length=180)
    slug: str = Field(min_length=2, max_length=180)
    short_description: str = Field(min_length=10, max_length=320)
    description: str = Field(min_length=20)
    icon: str = Field(default="Package", max_length=80)
    image_url: str | None = Field(default=None, max_length=500)
    image_object_key: str | None = Field(default=None, max_length=500)
    image_original_filename: str | None = Field(default=None, max_length=255)
    featured: bool = False
    sort_order: int = 0
    is_active: bool = True


class ServiceCreate(ServiceBase):
    pass


class ServiceUpdate(BaseSchema):
    title: str | None = Field(default=None, min_length=2, max_length=180)
    slug: str | None = Field(default=None, min_length=2, max_length=180)
    short_description: str | None = Field(default=None, min_length=10, max_length=320)
    description: str | None = Field(default=None, min_length=20)
    icon: str | None = Field(default=None, max_length=80)
    image_url: str | None = Field(default=None, max_length=500)
    image_object_key: str | None = Field(default=None, max_length=500)
    image_original_filename: str | None = Field(default=None, max_length=255)
    featured: bool | None = None
    sort_order: int | None = None
    is_active: bool | None = None


class Service(ServiceBase, TimestampMixin):
    id: int


class NewsPostBase(BaseSchema):
    title: str = Field(min_length=2, max_length=220)
    slug: str = Field(min_length=2, max_length=220)
    excerpt: str = Field(min_length=10, max_length=360)
    body: str = Field(min_length=20)
    category: str = Field(default="Company News", min_length=2, max_length=80)
    image_url: str | None = Field(default=None, max_length=500)
    published: bool = True


class NewsPostCreate(NewsPostBase):
    pass


class NewsPostUpdate(BaseSchema):
    title: str | None = Field(default=None, min_length=2, max_length=220)
    slug: str | None = Field(default=None, min_length=2, max_length=220)
    excerpt: str | None = Field(default=None, min_length=10, max_length=360)
    body: str | None = Field(default=None, min_length=20)
    category: str | None = Field(default=None, min_length=2, max_length=80)
    image_url: str | None = Field(default=None, max_length=500)
    published: bool | None = None


class NewsPost(NewsPostBase, TimestampMixin):
    id: int
    published_at: datetime


class TestimonialBase(BaseSchema):
    client_name: str = Field(min_length=2, max_length=160)
    company: str = Field(min_length=2, max_length=180)
    quote: str = Field(min_length=20)
    rating: int = Field(default=5, ge=1, le=5)
    is_active: bool = True


class TestimonialCreate(TestimonialBase):
    pass


class TestimonialUpdate(BaseSchema):
    client_name: str | None = Field(default=None, min_length=2, max_length=160)
    company: str | None = Field(default=None, min_length=2, max_length=180)
    quote: str | None = Field(default=None, min_length=20)
    rating: int | None = Field(default=None, ge=1, le=5)
    is_active: bool | None = None


class Testimonial(TestimonialBase, TimestampMixin):
    id: int


class TeamMemberBase(BaseSchema):
    full_name: str = Field(min_length=2, max_length=160)
    role: str = Field(min_length=2, max_length=160)
    bio: str = Field(min_length=20)
    image_url: str | None = Field(default=None, max_length=500)
    sort_order: int = 0
    is_active: bool = True


class TeamMemberCreate(TeamMemberBase):
    pass


class TeamMemberUpdate(BaseSchema):
    full_name: str | None = Field(default=None, min_length=2, max_length=160)
    role: str | None = Field(default=None, min_length=2, max_length=160)
    bio: str | None = Field(default=None, min_length=20)
    image_url: str | None = Field(default=None, max_length=500)
    sort_order: int | None = None
    is_active: bool | None = None


class TeamMember(TeamMemberBase, TimestampMixin):
    id: int


class InquiryCreate(BaseSchema):
    full_name: str = Field(min_length=2, max_length=160)
    email: str = Field(min_length=5, max_length=255, pattern=r"^[^@\s]+@[^@\s]+\.[^@\s]+$")
    phone: str | None = Field(default=None, max_length=80)
    company: str | None = Field(default=None, max_length=180)
    service_interest: str = Field(min_length=2, max_length=180)
    message: str = Field(min_length=10)


class InquiryUpdate(BaseSchema):
    status: str = Field(min_length=2, max_length=40)


class Inquiry(InquiryCreate, TimestampMixin):
    id: int
    status: str


class DashboardBlock(BaseSchema):
    type: str = Field(min_length=2, max_length=40)
    content: str = Field(default="")
    metadata: dict[str, Any] = Field(default_factory=dict)


class DashboardPageBase(BaseSchema):
    title: str = Field(min_length=2, max_length=180)
    slug: str = Field(min_length=2, max_length=180)
    icon: str = Field(default="FileText", max_length=80)
    blocks: list[DashboardBlock] = Field(default_factory=list)
    sort_order: int = 0


class DashboardPageCreate(DashboardPageBase):
    pass


class DashboardPageUpdate(BaseSchema):
    title: str | None = Field(default=None, min_length=2, max_length=180)
    slug: str | None = Field(default=None, min_length=2, max_length=180)
    icon: str | None = Field(default=None, max_length=80)
    blocks: list[DashboardBlock] | None = None
    sort_order: int | None = None


class DashboardPage(DashboardPageBase):
    id: int
    created_at: datetime
    updated_at: datetime


class ShipmentStage(BaseSchema):
    label: str = Field(min_length=2, max_length=120)
    description: str = Field(default="", max_length=300)
    completed: bool = False
    timestamp: str | None = Field(default=None, max_length=80)


class ShipmentBase(BaseSchema):
    reference_number: str = Field(min_length=3, max_length=80)
    customer_name: str = Field(min_length=2, max_length=160)
    route: str = Field(min_length=2, max_length=220)
    current_stage: str = Field(min_length=2, max_length=120)
    status: str = Field(default="In Transit", min_length=2, max_length=60)
    stages: list[ShipmentStage] = Field(default_factory=list)


class ShipmentCreate(ShipmentBase):
    pass


class ShipmentUpdate(BaseSchema):
    reference_number: str | None = Field(default=None, min_length=3, max_length=80)
    customer_name: str | None = Field(default=None, min_length=2, max_length=160)
    route: str | None = Field(default=None, min_length=2, max_length=220)
    current_stage: str | None = Field(default=None, min_length=2, max_length=120)
    status: str | None = Field(default=None, min_length=2, max_length=60)
    stages: list[ShipmentStage] | None = None


class Shipment(ShipmentBase):
    id: int
    created_at: datetime
    updated_at: datetime


class ShipmentTrackingResponse(BaseSchema):
    reference_number: str
    customer_name: str
    route: str
    current_stage: str
    status: str
    stages: list[ShipmentStage]
    updated_at: datetime


class NewsletterSubscriberCreate(BaseSchema):
    email: str = Field(min_length=5, max_length=255, pattern=r"^[^@\s]+@[^@\s]+\.[^@\s]+$")
    full_name: str | None = Field(default=None, max_length=160)
    source: str = Field(default="website", max_length=80)


class NewsletterSubscriberUpdate(BaseSchema):
    full_name: str | None = Field(default=None, max_length=160)
    source: str | None = Field(default=None, max_length=80)
    is_active: bool | None = None


class NewsletterSubscriber(NewsletterSubscriberCreate, TimestampMixin):
    id: int
    is_active: bool


class AdminUserBase(BaseSchema):
    username: str = Field(min_length=3, max_length=80)
    full_name: str = Field(min_length=2, max_length=160)
    role: str = Field(default="editor", min_length=2, max_length=40)
    delegated_pages: list[str] = Field(default_factory=list)
    is_active: bool = True


class AdminUserCreate(AdminUserBase):
    password: str = Field(min_length=6, max_length=200)


class AdminUserUpdate(BaseSchema):
    username: str | None = Field(default=None, min_length=3, max_length=80)
    full_name: str | None = Field(default=None, min_length=2, max_length=160)
    password: str | None = Field(default=None, min_length=6, max_length=200)
    role: str | None = Field(default=None, min_length=2, max_length=40)
    delegated_pages: list[str] | None = None
    is_active: bool | None = None


class AdminUser(AdminUserBase, TimestampMixin):
    id: int


class PublicSitePayload(BaseSchema):
    settings: dict[str, str]
    services: list[Service]
    news: list[NewsPost]
    testimonials: list[Testimonial]
    team: list[TeamMember]
    dashboard_pages: list[DashboardPage]


class DashboardStats(BaseSchema):
    services: int
    news_posts: int
    inquiries: int
    new_inquiries: int
    testimonials: int
    shipments: int = 0
    newsletter_subscribers: int = 0
    admin_users: int = 0
