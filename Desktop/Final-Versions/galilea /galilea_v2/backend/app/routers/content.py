from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from .. import models, schemas
from ..database import get_db

router = APIRouter(prefix="/content", tags=["content"])


@router.get("", response_model=schemas.PublicSitePayload)
def get_public_site_content(db: Session = Depends(get_db)):
    settings = {
        item.key: item.value
        for item in db.query(models.SiteSetting).order_by(models.SiteSetting.group_name, models.SiteSetting.key).all()
    }
    services = (
        db.query(models.Service)
        .filter(models.Service.is_active.is_(True))
        .order_by(models.Service.sort_order, models.Service.title)
        .all()
    )
    news = (
        db.query(models.NewsPost)
        .filter(models.NewsPost.published.is_(True))
        .order_by(models.NewsPost.published_at.desc())
        .limit(6)
        .all()
    )
    testimonials = (
        db.query(models.Testimonial)
        .filter(models.Testimonial.is_active.is_(True))
        .order_by(models.Testimonial.created_at.desc())
        .all()
    )
    team = (
        db.query(models.TeamMember)
        .filter(models.TeamMember.is_active.is_(True))
        .order_by(models.TeamMember.sort_order, models.TeamMember.full_name)
        .all()
    )
    dashboard_pages = (
        db.query(models.DashboardPage)
        .order_by(models.DashboardPage.sort_order, models.DashboardPage.title)
        .all()
    )
    return {
        "settings": settings,
        "services": services,
        "news": news,
        "testimonials": testimonials,
        "team": team,
        "dashboard_pages": dashboard_pages,
    }


@router.post("/inquiries", response_model=schemas.Inquiry, status_code=status.HTTP_201_CREATED)
def create_inquiry(payload: schemas.InquiryCreate, db: Session = Depends(get_db)):
    inquiry = models.Inquiry(**payload.model_dump())
    db.add(inquiry)
    db.commit()
    db.refresh(inquiry)
    return inquiry


@router.post("/newsletter", response_model=schemas.NewsletterSubscriber, status_code=status.HTTP_201_CREATED)
def subscribe_newsletter(payload: schemas.NewsletterSubscriberCreate, db: Session = Depends(get_db)):
    subscriber = models.NewsletterSubscriber(**payload.model_dump())
    subscriber.email = subscriber.email.lower()
    db.add(subscriber)
    try:
        db.commit()
    except IntegrityError as exc:
        db.rollback()
        existing = db.query(models.NewsletterSubscriber).filter(models.NewsletterSubscriber.email == payload.email.lower()).first()
        if existing:
            existing.is_active = True
            if payload.full_name:
                existing.full_name = payload.full_name
            db.commit()
            db.refresh(existing)
            return existing
        raise HTTPException(status_code=status.HTTP_409_CONFLICT, detail="This email is already subscribed.") from exc
    db.refresh(subscriber)
    return subscriber


@router.get("/services/{slug}", response_model=schemas.Service)
def get_service(slug: str, db: Session = Depends(get_db)):
    service = (
        db.query(models.Service)
        .filter(models.Service.slug == slug, models.Service.is_active.is_(True))
        .first()
    )
    if service is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Service not found")
    return service


@router.get("/news/{slug}", response_model=schemas.NewsPost)
def get_news_post(slug: str, db: Session = Depends(get_db)):
    post = (
        db.query(models.NewsPost)
        .filter(models.NewsPost.slug == slug, models.NewsPost.published.is_(True))
        .first()
    )
    if post is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="News post not found")
    return post
