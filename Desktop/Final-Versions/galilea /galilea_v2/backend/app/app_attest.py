from fastapi import APIRouter
from starlette.middleware.base import BaseHTTPMiddleware

router = APIRouter(prefix="/app-attest", tags=["app-attest"])


class AppAttestMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request, call_next):
        return await call_next(request)


@router.get("/health")
def app_attest_health():
    return {"status": "available"}
