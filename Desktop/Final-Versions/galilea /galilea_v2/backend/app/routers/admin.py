import hashlib
import os
import secrets

from fastapi import APIRouter, Depends, Header, HTTPException, status
from pydantic import BaseModel, Field
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from .. import models, schemas
from ..database import get_db

router = APIRouter(prefix="/admin", tags=["admin"])


class AdminLoginRequest(BaseModel):
    username: str = Field(min_length=1, max_length=80)
    password: str = Field(min_length=1, max_length=200)


class AdminLoginResponse(BaseModel):
    token: str


def _admin_username() -> str:
    return os.getenv("ADMIN_USERNAME") or "admin"


def _admin_password() -> str:
    return os.getenv("ADMIN_PASSWORD") or os.getenv("ADMIN_TOKEN") or "Galilea2026"


def _admin_token() -> str:
    configured = os.getenv("ADMIN_TOKEN")
    if configured:
        return configured
    return hashlib.sha256(f"{_admin_username()}:{_admin_password()}".encode("utf-8")).hexdigest()


def _password_hash(password: str) -> str:
    pepper = os.getenv("ADMIN_PASSWORD_PEPPER") or "galilea-admin"
    return hashlib.sha256(f"{pepper}:{password}".encode("utf-8")).hexdigest()


def _user_token(user: models.AdminUser) -> str:
    return f"user:{user.username}:{hashlib.sha256(f'{user.username}:{user.password_hash}'.encode('utf-8')).hexdigest()}"


def verify_admin_token(x_admin_token: str | None = Header(default=None), db: Session = Depends(get_db)):
    if not x_admin_token:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Valid admin token required")
    if secrets.compare_digest(x_admin_token, _admin_token()):
        return {"username": _admin_username(), "role": "owner", "delegated_pages": ["*"]}
    if x_admin_token.startswith("user:"):
        parts = x_admin_token.split(":", 2)
        if len(parts) == 3:
            user = db.query(models.AdminUser).filter(models.AdminUser.username == parts[1], models.AdminUser.is_active.is_(True)).first()
            if user and secrets.compare_digest(x_admin_token, _user_token(user)):
                return {"username": user.username, "role": user.role, "delegated_pages": user.delegated_pages or []}
    raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Valid admin token required")


@router.post("/login", response_model=AdminLoginResponse)
def login_admin(payload: AdminLoginRequest, db: Session = Depends(get_db)):
    username = payload.username.strip()
    user = db.query(models.AdminUser).filter(models.AdminUser.username == username, models.AdminUser.is_active.is_(True)).first()
    if user and secrets.compare_digest(user.password_hash, _password_hash(payload.password)):
        return {"token": _user_token(user)}
    if secrets.compare_digest(username, _admin_username()) and secrets.compare_digest(payload.password, _admin_password()):
        return {"token": _admin_token()}
    raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid admin username or password")


def apply_updates(instance, updates: dict):
    for field, value in updates.items():
        setattr(instance, field, value)
    return instance


def commit_or_conflict(db: Session, instance):
    try:
        db.commit()
    except IntegrityError as exc:
        db.rollback()
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail="A record with this unique value already exists.",
        ) from exc
    db.refresh(instance)
    return instance


def get_or_404(db: Session, model, item_id: int, label: str):
    item = db.get(model, item_id)
    if item is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail=f"{label} not found")
    return item


@router.get("/stats", response_model=schemas.DashboardStats, dependencies=[Depends(verify_admin_token)])
def get_dashboard_stats(db: Session = Depends(get_db)):
    return {
        "services": db.query(models.Service.id).count(),
        "news_posts": db.query(models.NewsPost.id).count(),
        "inquiries": db.query(models.Inquiry.id).count(),
        "new_inquiries": db.query(models.Inquiry.id).filter(models.Inquiry.status == "new").count(),
        "testimonials": db.query(models.Testimonial.id).count(),
        "shipments": db.query(models.Shipment.id).count(),
        "newsletter_subscribers": db.query(models.NewsletterSubscriber.id).filter(models.NewsletterSubscriber.is_active.is_(True)).count(),
        "admin_users": db.query(models.AdminUser.id).count(),
    }


@router.get("/settings", response_model=list[schemas.SiteSetting], dependencies=[Depends(verify_admin_token)])
def list_settings(db: Session = Depends(get_db)):
    return db.query(models.SiteSetting).order_by(models.SiteSetting.group_name, models.SiteSetting.key).all()


@router.post("/settings", response_model=schemas.SiteSetting, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_setting(payload: schemas.SiteSettingCreate, db: Session = Depends(get_db)):
    item = models.SiteSetting(**payload.model_dump())
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/settings/{item_id}", response_model=schemas.SiteSetting, dependencies=[Depends(verify_admin_token)])
def update_setting(item_id: int, payload: schemas.SiteSettingUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.SiteSetting, item_id, "Setting")
    apply_updates(item, payload.model_dump(exclude_unset=True))
    return commit_or_conflict(db, item)


@router.get("/services", response_model=list[schemas.Service], dependencies=[Depends(verify_admin_token)])
def list_services(db: Session = Depends(get_db)):
    return db.query(models.Service).order_by(models.Service.sort_order, models.Service.title).all()


@router.post("/services", response_model=schemas.Service, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_service(payload: schemas.ServiceCreate, db: Session = Depends(get_db)):
    item = models.Service(**payload.model_dump())
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/services/{item_id}", response_model=schemas.Service, dependencies=[Depends(verify_admin_token)])
def update_service(item_id: int, payload: schemas.ServiceUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.Service, item_id, "Service")
    apply_updates(item, payload.model_dump(exclude_unset=True))
    return commit_or_conflict(db, item)


@router.delete("/services/{item_id}", status_code=status.HTTP_204_NO_CONTENT, dependencies=[Depends(verify_admin_token)])
def delete_service(item_id: int, db: Session = Depends(get_db)):
    item = get_or_404(db, models.Service, item_id, "Service")
    db.delete(item)
    db.commit()


@router.get("/news", response_model=list[schemas.NewsPost], dependencies=[Depends(verify_admin_token)])
def list_news(db: Session = Depends(get_db)):
    return db.query(models.NewsPost).order_by(models.NewsPost.published_at.desc()).all()


@router.post("/news", response_model=schemas.NewsPost, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_news(payload: schemas.NewsPostCreate, db: Session = Depends(get_db)):
    item = models.NewsPost(**payload.model_dump())
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/news/{item_id}", response_model=schemas.NewsPost, dependencies=[Depends(verify_admin_token)])
def update_news(item_id: int, payload: schemas.NewsPostUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.NewsPost, item_id, "News post")
    apply_updates(item, payload.model_dump(exclude_unset=True))
    return commit_or_conflict(db, item)


@router.delete("/news/{item_id}", status_code=status.HTTP_204_NO_CONTENT, dependencies=[Depends(verify_admin_token)])
def delete_news(item_id: int, db: Session = Depends(get_db)):
    item = get_or_404(db, models.NewsPost, item_id, "News post")
    db.delete(item)
    db.commit()


@router.get("/testimonials", response_model=list[schemas.Testimonial], dependencies=[Depends(verify_admin_token)])
def list_testimonials(db: Session = Depends(get_db)):
    return db.query(models.Testimonial).order_by(models.Testimonial.created_at.desc()).all()


@router.post("/testimonials", response_model=schemas.Testimonial, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_testimonial(payload: schemas.TestimonialCreate, db: Session = Depends(get_db)):
    item = models.Testimonial(**payload.model_dump())
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/testimonials/{item_id}", response_model=schemas.Testimonial, dependencies=[Depends(verify_admin_token)])
def update_testimonial(item_id: int, payload: schemas.TestimonialUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.Testimonial, item_id, "Testimonial")
    apply_updates(item, payload.model_dump(exclude_unset=True))
    return commit_or_conflict(db, item)


@router.get("/team", response_model=list[schemas.TeamMember], dependencies=[Depends(verify_admin_token)])
def list_team(db: Session = Depends(get_db)):
    return db.query(models.TeamMember).order_by(models.TeamMember.sort_order, models.TeamMember.full_name).all()


@router.post("/team", response_model=schemas.TeamMember, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_team_member(payload: schemas.TeamMemberCreate, db: Session = Depends(get_db)):
    item = models.TeamMember(**payload.model_dump())
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/team/{item_id}", response_model=schemas.TeamMember, dependencies=[Depends(verify_admin_token)])
def update_team_member(item_id: int, payload: schemas.TeamMemberUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.TeamMember, item_id, "Team member")
    apply_updates(item, payload.model_dump(exclude_unset=True))
    return commit_or_conflict(db, item)


@router.get("/inquiries", response_model=list[schemas.Inquiry], dependencies=[Depends(verify_admin_token)])
def list_inquiries(db: Session = Depends(get_db)):
    return db.query(models.Inquiry).order_by(models.Inquiry.created_at.desc()).all()


@router.put("/inquiries/{item_id}", response_model=schemas.Inquiry, dependencies=[Depends(verify_admin_token)])
def update_inquiry(item_id: int, payload: schemas.InquiryUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.Inquiry, item_id, "Inquiry")
    item.status = payload.status
    return commit_or_conflict(db, item)


@router.get("/pages", response_model=list[schemas.DashboardPage], dependencies=[Depends(verify_admin_token)])
def list_pages(db: Session = Depends(get_db)):
    return db.query(models.DashboardPage).order_by(models.DashboardPage.sort_order, models.DashboardPage.title).all()


@router.post("/pages", response_model=schemas.DashboardPage, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_page(payload: schemas.DashboardPageCreate, db: Session = Depends(get_db)):
    item = models.DashboardPage(**payload.model_dump(mode="json"))
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/pages/{item_id}", response_model=schemas.DashboardPage, dependencies=[Depends(verify_admin_token)])
def update_page(item_id: int, payload: schemas.DashboardPageUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.DashboardPage, item_id, "Dashboard page")
    apply_updates(item, payload.model_dump(exclude_unset=True, mode="json"))
    return commit_or_conflict(db, item)


@router.get("/shipments", response_model=list[schemas.Shipment], dependencies=[Depends(verify_admin_token)])
def list_shipments(db: Session = Depends(get_db)):
    return db.query(models.Shipment).order_by(models.Shipment.updated_at.desc()).all()


@router.post("/shipments", response_model=schemas.Shipment, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_shipment(payload: schemas.ShipmentCreate, db: Session = Depends(get_db)):
    data = payload.model_dump(mode="json")
    data["reference_number"] = data["reference_number"].strip().upper()
    item = models.Shipment(**data)
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/shipments/{item_id}", response_model=schemas.Shipment, dependencies=[Depends(verify_admin_token)])
def update_shipment(item_id: int, payload: schemas.ShipmentUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.Shipment, item_id, "Shipment")
    data = payload.model_dump(exclude_unset=True, mode="json")
    if "reference_number" in data and data["reference_number"]:
        data["reference_number"] = str(data["reference_number"]).strip().upper()
    apply_updates(item, data)
    return commit_or_conflict(db, item)


@router.delete("/shipments/{item_id}", status_code=status.HTTP_204_NO_CONTENT, dependencies=[Depends(verify_admin_token)])
def delete_shipment(item_id: int, db: Session = Depends(get_db)):
    item = get_or_404(db, models.Shipment, item_id, "Shipment")
    db.delete(item)
    db.commit()


@router.get("/newsletter", response_model=list[schemas.NewsletterSubscriber], dependencies=[Depends(verify_admin_token)])
def list_newsletter_subscribers(db: Session = Depends(get_db)):
    return db.query(models.NewsletterSubscriber).order_by(models.NewsletterSubscriber.created_at.desc()).all()


@router.put("/newsletter/{item_id}", response_model=schemas.NewsletterSubscriber, dependencies=[Depends(verify_admin_token)])
def update_newsletter_subscriber(item_id: int, payload: schemas.NewsletterSubscriberUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.NewsletterSubscriber, item_id, "Newsletter subscriber")
    apply_updates(item, payload.model_dump(exclude_unset=True))
    return commit_or_conflict(db, item)


@router.delete("/newsletter/{item_id}", status_code=status.HTTP_204_NO_CONTENT, dependencies=[Depends(verify_admin_token)])
def delete_newsletter_subscriber(item_id: int, db: Session = Depends(get_db)):
    item = get_or_404(db, models.NewsletterSubscriber, item_id, "Newsletter subscriber")
    db.delete(item)
    db.commit()


@router.get("/users", response_model=list[schemas.AdminUser], dependencies=[Depends(verify_admin_token)])
def list_admin_users(db: Session = Depends(get_db)):
    return db.query(models.AdminUser).order_by(models.AdminUser.created_at.desc()).all()


@router.post("/users", response_model=schemas.AdminUser, status_code=status.HTTP_201_CREATED, dependencies=[Depends(verify_admin_token)])
def create_admin_user(payload: schemas.AdminUserCreate, db: Session = Depends(get_db)):
    data = payload.model_dump(mode="json")
    password = data.pop("password")
    data["username"] = data["username"].strip()
    data["password_hash"] = _password_hash(password)
    item = models.AdminUser(**data)
    db.add(item)
    return commit_or_conflict(db, item)


@router.put("/users/{item_id}", response_model=schemas.AdminUser, dependencies=[Depends(verify_admin_token)])
def update_admin_user(item_id: int, payload: schemas.AdminUserUpdate, db: Session = Depends(get_db)):
    item = get_or_404(db, models.AdminUser, item_id, "Admin user")
    data = payload.model_dump(exclude_unset=True, mode="json")
    if "password" in data:
        password = data.pop("password")
        if password:
            data["password_hash"] = _password_hash(password)
    if "username" in data and data["username"]:
        data["username"] = str(data["username"]).strip()
    apply_updates(item, data)
    return commit_or_conflict(db, item)


@router.delete("/users/{item_id}", status_code=status.HTTP_204_NO_CONTENT, dependencies=[Depends(verify_admin_token)])
def delete_admin_user(item_id: int, db: Session = Depends(get_db)):
    item = get_or_404(db, models.AdminUser, item_id, "Admin user")
    db.delete(item)
    db.commit()
