# Skill: Security Review

## Checklist

### Backend
- Validate input (DTOs)
- Check auth rules in security.yaml
- Ensure no sensitive data exposure
- Rate limiting applied

### Database
- Prevent N+1 queries
- Validate indexes

### Frontend
- No token leaks
- Proper auth handling
- XSS prevention

## Output
- Vulnerabilities
- Severity (Critical/High/Medium/Low)
- Fix recommendations
