# CRITICAL: Always import Base from app.database - DO NOT create your own Base!
from app.database import Base

from sqlalchemy import Boolean, Column, DateTime, Integer, JSON, String, Text, func


class SiteSetting(Base):
    __tablename__ = "site_settings"

    id = Column(Integer, primary_key=True)
    key = Column(String(100), unique=True, nullable=False, index=True)
    label = Column(String(160), nullable=False)
    value = Column(Text, nullable=False)
    group_name = Column(String(80), nullable=False, default="general")
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class Service(Base):
    __tablename__ = "services"

    id = Column(Integer, primary_key=True)
    title = Column(String(180), nullable=False)
    slug = Column(String(180), unique=True, nullable=False, index=True)
    short_description = Column(String(320), nullable=False)
    description = Column(Text, nullable=False)
    icon = Column(String(80), nullable=False, default="Package")
    image_url = Column(String(500), nullable=True)
    image_object_key = Column(String(500), nullable=True)
    image_original_filename = Column(String(255), nullable=True)
    featured = Column(Boolean, nullable=False, default=False)
    sort_order = Column(Integer, nullable=False, default=0)
    is_active = Column(Boolean, nullable=False, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class NewsPost(Base):
    __tablename__ = "news_posts"

    id = Column(Integer, primary_key=True)
    title = Column(String(220), nullable=False)
    slug = Column(String(220), unique=True, nullable=False, index=True)
    excerpt = Column(String(360), nullable=False)
    body = Column(Text, nullable=False)
    category = Column(String(80), nullable=False, default="Company News")
    image_url = Column(String(500), nullable=True)
    published = Column(Boolean, nullable=False, default=True)
    published_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class Testimonial(Base):
    __tablename__ = "testimonials"

    id = Column(Integer, primary_key=True)
    client_name = Column(String(160), nullable=False)
    company = Column(String(180), nullable=False)
    quote = Column(Text, nullable=False)
    rating = Column(Integer, nullable=False, default=5)
    is_active = Column(Boolean, nullable=False, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class TeamMember(Base):
    __tablename__ = "team_members"

    id = Column(Integer, primary_key=True)
    full_name = Column(String(160), nullable=False)
    role = Column(String(160), nullable=False)
    bio = Column(Text, nullable=False)
    image_url = Column(String(500), nullable=True)
    sort_order = Column(Integer, nullable=False, default=0)
    is_active = Column(Boolean, nullable=False, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class Inquiry(Base):
    __tablename__ = "inquiries"

    id = Column(Integer, primary_key=True)
    full_name = Column(String(160), nullable=False)
    email = Column(String(255), nullable=False)
    phone = Column(String(80), nullable=True)
    company = Column(String(180), nullable=True)
    service_interest = Column(String(180), nullable=False)
    message = Column(Text, nullable=False)
    status = Column(String(40), nullable=False, default="new")
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class DashboardPage(Base):
    __tablename__ = "dashboard_pages"

    id = Column(Integer, primary_key=True)
    title = Column(String(180), nullable=False)
    slug = Column(String(180), unique=True, nullable=False, index=True)
    icon = Column(String(80), nullable=False, default="FileText")
    blocks = Column(JSON, nullable=False, default=list)
    sort_order = Column(Integer, nullable=False, default=0)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)


class Shipment(Base):
    __tablename__ = "shipments"

    id = Column(Integer, primary_key=True)
    reference_number = Column(String(80), unique=True, nullable=False, index=True)
    customer_name = Column(String(160), nullable=False)
    route = Column(String(220), nullable=False)
    current_stage = Column(String(120), nullable=False)
    status = Column(String(60), nullable=False, default="In Transit")
    stages = Column(JSON, nullable=False, default=list)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)


class NewsletterSubscriber(Base):
    __tablename__ = "newsletter_subscribers"

    id = Column(Integer, primary_key=True)
    email = Column(String(255), unique=True, nullable=False, index=True)
    full_name = Column(String(160), nullable=True)
    source = Column(String(80), nullable=False, default="website")
    is_active = Column(Boolean, nullable=False, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)


class AdminUser(Base):
    __tablename__ = "admin_users"

    id = Column(Integer, primary_key=True)
    username = Column(String(80), unique=True, nullable=False, index=True)
    full_name = Column(String(160), nullable=False)
    password_hash = Column(String(128), nullable=False)
    role = Column(String(40), nullable=False, default="editor")
    delegated_pages = Column(JSON, nullable=False, default=list)
    is_active = Column(Boolean, nullable=False, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now(), nullable=False)
