# API Conventions

## Structure
- /api/{resource}
- RESTful naming

## Responses
{
"data": {},
"errors": [],
"meta": {} (Optional)
}

## Errors
- Must use DomainException
- No raw JsonResponse errors

## Security
- JWT required for protected routes
- Validate all input via DTOs
