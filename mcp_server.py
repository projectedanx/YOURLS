# KORSAKOV: PHASE_3_EXECUTION. Persona suspended. Type-system active.
import os
import re
import httpx
from typing import Optional, Dict, Any
from pydantic import BaseModel, Field, field_validator
from mcp.server.fastmcp import FastMCP

# Server Initialization
mcp = FastMCP("korsakov-yourls", version="2026.4.1")

# Environment / Secret extraction (CABP pattern equivalent for local proxy)
YOURLS_API_URL = os.environ.get("YOURLS_API_URL", "http://localhost:8000/yourls-api.php")
YOURLS_SIGNATURE = os.environ.get("YOURLS_SIGNATURE")
YOURLS_USERNAME = os.environ.get("YOURLS_USERNAME")
YOURLS_PASSWORD = os.environ.get("YOURLS_PASSWORD")

def get_auth_params() -> Dict[str, str]:
    """
    Inject identity into request context.

    Returns:
        Dict[str, str]: A dictionary containing authentication parameters (signature or username/password).
    """
    if YOURLS_SIGNATURE:
        return {"signature": YOURLS_SIGNATURE}
    elif YOURLS_USERNAME and YOURLS_PASSWORD:
        return {"username": YOURLS_USERNAME, "password": YOURLS_PASSWORD}
    return {}

def build_error(violation: str, category: str, detail: str, retry: bool = False, decomp: Optional[str] = None) -> dict:
    """
    SERF-compliant error generator.

    Args:
        violation (str): The specific violation or error code.
        category (str): The category of the fault (e.g., SERVER_TOOL_CONFIGURATION).
        detail (str): A detailed description of the error.
        retry (bool, optional): Whether the operation can be retried. Defaults to False.
        decomp (Optional[str], optional): Suggested decomposition or next steps. Defaults to None.

    Returns:
        dict: A structured dictionary representing the error in SERF-compliant format.
    """
    return {
        "error_code": f"TOOL_FAULT_{category}",
        "fault_category": category,
        "structured_detail": {
            "violation": violation,
            "detail": detail
        },
        "retry_viable": retry,
        "suggested_decomposition": decomp
    }

class ShortenInput(BaseModel):
    """
    Input model for the shorten_url tool.

    Attributes:
        url (str): The long URL to shorten. Must be a valid HTTP/HTTPS URL.
        keyword (Optional[str]): Optional custom keyword for the short URL.
        title (Optional[str]): Optional custom title for the URL.
    """
    url: str = Field(
        max_length=2048,
        description="The long URL to shorten. Must be a valid HTTP/HTTPS URL."
    )
    keyword: Optional[str] = Field(
        default=None,
        max_length=200,
        description="Optional custom keyword for the short URL."
    )
    title: Optional[str] = Field(
        default=None,
        max_length=200,
        description="Optional custom title for the URL."
    )

    @field_validator("url")
    @classmethod
    def must_be_url(cls, v: str) -> str:
        """
        Validate that the string is a valid HTTP/HTTPS URL.

        Args:
            v (str): The URL string to validate.

        Returns:
            str: The validated URL string.

        Raises:
            ValueError: If the URL does not start with http:// or https://.
        """
        if not re.match(r"^https?://", v, re.IGNORECASE):
            raise ValueError("URL must start with http:// or https://")
        return v

class ExpandInput(BaseModel):
    """
    Input model for the expand_url tool.

    Attributes:
        shorturl (str): The short URL or keyword to expand.
    """
    shorturl: str = Field(
        max_length=2048,
        description="The short URL or keyword to expand."
    )

class StatsInput(BaseModel):
    """
    Input model for the get_url_stats tool.

    Attributes:
        shorturl (str): The short URL or keyword to fetch stats for.
    """
    shorturl: str = Field(
        max_length=2048,
        description="The short URL or keyword to fetch stats for."
    )

@mcp.tool(
    description=(
        "PURPOSE: Shortens a long URL using the YOURLS API. "
        "GUIDELINES: Use this to create a short link. Provide a custom keyword if requested. "
        "LIMITATIONS: URLs max 2048 chars. Keywords max 200 chars. "
        "PARAMETERS: url (str, required); keyword (str, optional); title (str, optional)."
    )
)
async def shorten_url(url: str, keyword: Optional[str] = None, title: Optional[str] = None) -> dict:
    """
    Shorten a long URL using the YOURLS API.

    Args:
        url (str): The long URL to shorten.
        keyword (Optional[str], optional): A custom keyword for the short URL. Defaults to None.
        title (Optional[str], optional): A custom title for the short URL. Defaults to None.

    Returns:
        dict: The JSON response from the YOURLS API.
    """
    try:
        validated = ShortenInput(url=url, keyword=keyword, title=title)
    except ValueError as e:
         return build_error("VALIDATION_ERROR", "SERVER_TOOL_CONFIGURATION", str(e), False, "Correct input parameters.")

    params = {"action": "shorturl", "url": validated.url, "format": "json"}
    if validated.keyword:
        params["keyword"] = validated.keyword
    if validated.title:
        params["title"] = validated.title

    params.update(get_auth_params())

    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(YOURLS_API_URL, data=params, timeout=10.0)
            resp.raise_for_status()
            data = resp.json()
            return data
    except Exception as e:
        return build_error("UPSTREAM_ERROR", "SERVER_HOST_CONFIGURATION", str(e), True, "Verify YOURLS API endpoint.")

@mcp.tool(
    description=(
        "PURPOSE: Expands a short URL or keyword to its original long URL. "
        "GUIDELINES: Use this when you need to know the destination of a shortlink. "
        "LIMITATIONS: Input max 2048 chars. "
        "PARAMETERS: shorturl (str, required: the short URL or keyword)."
    )
)
async def expand_url(shorturl: str) -> dict:
    """
    Expand a short URL or keyword to its original long URL.

    Args:
        shorturl (str): The short URL or keyword to expand.

    Returns:
        dict: The JSON response from the YOURLS API.
    """
    try:
        validated = ExpandInput(shorturl=shorturl)
    except ValueError as e:
         return build_error("VALIDATION_ERROR", "SERVER_TOOL_CONFIGURATION", str(e), False, "Correct input parameters.")

    params = {"action": "expand", "shorturl": validated.shorturl, "format": "json"}
    params.update(get_auth_params())

    try:
        async with httpx.AsyncClient() as client:
            resp = await client.get(YOURLS_API_URL, params=params, timeout=10.0)
            resp.raise_for_status()
            data = resp.json()
            return data
    except Exception as e:
        return build_error("UPSTREAM_ERROR", "SERVER_HOST_CONFIGURATION", str(e), True, "Verify YOURLS API endpoint.")

@mcp.tool(
    description=(
        "PURPOSE: Retrieves statistics for a specific short URL. "
        "GUIDELINES: Use this to get click counts, date created, and other metadata. "
        "LIMITATIONS: Input max 2048 chars. "
        "PARAMETERS: shorturl (str, required: the short URL or keyword)."
    )
)
async def get_url_stats(shorturl: str) -> dict:
    """
    Retrieve statistics for a specific short URL.

    Args:
        shorturl (str): The short URL or keyword to fetch stats for.

    Returns:
        dict: The JSON response from the YOURLS API.
    """
    try:
        validated = StatsInput(shorturl=shorturl)
    except ValueError as e:
         return build_error("VALIDATION_ERROR", "SERVER_TOOL_CONFIGURATION", str(e), False, "Correct input parameters.")

    params = {"action": "url-stats", "shorturl": validated.shorturl, "format": "json"}
    params.update(get_auth_params())

    try:
        async with httpx.AsyncClient() as client:
            resp = await client.get(YOURLS_API_URL, params=params, timeout=10.0)
            resp.raise_for_status()
            data = resp.json()
            return data
    except Exception as e:
        return build_error("UPSTREAM_ERROR", "SERVER_HOST_CONFIGURATION", str(e), True, "Verify YOURLS API endpoint.")

if __name__ == "__main__":
    mcp.run(transport="stdio")
