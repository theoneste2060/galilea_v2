from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from .. import models, schemas
from ..database import get_db

router = APIRouter(prefix="/shipments", tags=["shipments"])


@router.get("/track/{reference_number}", response_model=schemas.ShipmentTrackingResponse)
def track_shipment(reference_number: str, db: Session = Depends(get_db)):
    cleaned_reference = reference_number.strip().upper()
    shipment = (
        db.query(models.Shipment)
        .filter(models.Shipment.reference_number == cleaned_reference)
        .first()
    )
    if shipment is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Shipment reference was not found. Please check the number and try again.",
        )
    return shipment
