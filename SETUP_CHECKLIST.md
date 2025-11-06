# ✅ AI Features - Setup Checklist

Use this checklist to verify your AI features setup.

## Pre-Setup

- [ ] Docker and Docker Compose installed
- [ ] Repository cloned
- [ ] DeepSeek account created (https://platform.deepseek.com/)
- [ ] DeepSeek API key obtained

## Configuration

- [ ] Add `DEEPSEEK_API_KEY` to `env` file or `docker-compose.yml`
- [ ] Verify Qdrant service in `docker-compose.yml`
- [ ] Check QDRANT_HOST and QDRANT_PORT environment variables

## Container Setup

- [ ] Run `docker-compose up -d`
- [ ] Verify 4 containers running: app, db, phpmyadmin, qdrant
- [ ] Check logs: `docker-compose logs -f`
- [ ] Test Qdrant: `curl http://localhost:6333/collections`

## Database

- [ ] Migration completed: `docker-compose exec app php spark migrate`
- [ ] Books table has new columns: pdf_file, has_vector, collection_name, etc.
- [ ] Seed data loaded (optional): `docker-compose exec app php spark db:seed InitialSeeder`

## Access Verification

- [ ] App accessible: http://localhost:8080
- [ ] Qdrant dashboard accessible: http://localhost:6333/dashboard
- [ ] phpMyAdmin accessible: http://localhost:8081
- [ ] Login works (admin@elibrary.com / Admin123)

## Upload Test (Admin)

- [ ] Login as admin
- [ ] Navigate to "Upload Buku"
- [ ] Fill form with test data
- [ ] Upload sample PDF (< 20MB)
- [ ] Wait for processing (~10-20 seconds)
- [ ] Verify badges appear on book card:
  - [ ] "PDF" badge visible
  - [ ] "AI" badge visible

## Processing Verification

- [ ] Check logs: `docker-compose logs app | grep "PDF processed"`
- [ ] Verify in database: `has_vector = 1`
- [ ] Check Qdrant dashboard: collection created
- [ ] Verify collection name format: `book_{id}`

## Chat Test (User)

- [ ] Logout from admin
- [ ] Login as user (user@elibrary.com / User123)
- [ ] Find book with AI badge
- [ ] Click "Tanya AI" button
- [ ] Detail page loads with chat interface
- [ ] Try suggested question (click button)
- [ ] AI responds in 2-4 seconds
- [ ] Response is relevant to book content
- [ ] Type custom question
- [ ] AI responds correctly

## UI Elements Check

### Dashboard
- [ ] Book cards display correctly
- [ ] "Tanya AI" button visible on all books
- [ ] PDF badge shows when PDF uploaded
- [ ] AI badge shows when processing complete

### Upload Form
- [ ] PDF file input visible
- [ ] File size validation works (20MB max)
- [ ] File type validation works (.pdf only)
- [ ] AI-Ready indicator appears
- [ ] Form submits successfully

### Detail/Chat Page
- [ ] Book information sidebar displays correctly
- [ ] Book cover image shows
- [ ] Title, author, description visible
- [ ] PDF status shows
- [ ] AI ready badge displays
- [ ] Chat area loads
- [ ] Welcome message appears
- [ ] Suggested questions show (3 buttons)
- [ ] Chat input field functional
- [ ] Send button works
- [ ] Typing indicator animates
- [ ] Messages display with avatars
- [ ] User messages align right (green)
- [ ] AI messages align left (blue)
- [ ] Timestamps show
- [ ] Error messages display if issues occur
- [ ] Responsive on mobile devices

## Error Handling

- [ ] Upload non-PDF file → shows error
- [ ] Upload file > 20MB → shows error
- [ ] Ask question on book without PDF → appropriate message
- [ ] Invalid API key → error logged
- [ ] Qdrant connection fail → error handled gracefully

## Performance Check

- [ ] PDF upload completes in < 30 seconds
- [ ] Chat response time < 5 seconds
- [ ] No memory leaks (check `docker stats`)
- [ ] Logs don't show repeated errors

## Security Verification

- [ ] Unauthenticated users can't access chat
- [ ] Regular users can't upload PDFs
- [ ] API key not exposed in client-side code
- [ ] File uploads restricted to /uploads/pdfs/
- [ ] SQL injection prevented (parameterized queries)

## Documentation Check

- [ ] AI_FEATURES.md exists and complete
- [ ] QUICK_START_AI.md exists
- [ ] IMPLEMENTATION_STATUS.md exists
- [ ] README.md updated with AI section
- [ ] Comments in code are clear

## Optional Advanced Tests

- [ ] Upload multiple PDFs
- [ ] Test with large PDF (15-20MB)
- [ ] Test with PDF in different language
- [ ] Test with scanned PDF (may not extract text)
- [ ] Test concurrent questions from multiple users
- [ ] Monitor API usage in DeepSeek dashboard
- [ ] Check Qdrant storage size
- [ ] Test on different browsers (Chrome, Firefox, Safari)

## Cleanup Test

- [ ] Delete book → Qdrant collection deleted
- [ ] Re-upload same book → works correctly
- [ ] Clear logs → logs writable and rotate

## Common Issues Resolution

### Qdrant not connecting
- [ ] Container running: `docker-compose ps`
- [ ] Restart: `docker-compose restart qdrant`
- [ ] Check network: `docker network ls`

### DeepSeek API error
- [ ] Verify API key set: `docker-compose exec app printenv | grep DEEPSEEK`
- [ ] Check API key valid at DeepSeek dashboard
- [ ] Restart app: `docker-compose restart app`

### PDF processing stuck
- [ ] Check logs: `docker-compose logs app | tail -100`
- [ ] Verify PDF not corrupt
- [ ] Check file permissions in /uploads/pdfs/
- [ ] Try smaller PDF

### Chat not loading
- [ ] Browser console for JavaScript errors
- [ ] Check route exists: `docker-compose exec app php spark routes`
- [ ] Verify book has has_vector=1
- [ ] Check AJAX endpoint: `/ai/chat`

## Final Verification

- [ ] All 4 containers healthy
- [ ] No errors in logs
- [ ] Can upload PDF successfully
- [ ] Can ask questions and get answers
- [ ] UI looks good and responsive
- [ ] Documentation complete

---

## 🎉 Success Criteria

Your setup is complete when:

✅ You can upload a PDF as admin
✅ The "AI" badge appears on the book card
✅ You can open the chat interface
✅ You can ask questions and receive relevant answers
✅ The system performs without errors

## 📊 Quick Health Check

Run this one-liner to check everything:

```bash
echo "=== Container Status ===" && \
docker-compose ps && \
echo -e "\n=== Qdrant Health ===" && \
curl -s http://localhost:6333/collections | head -1 && \
echo -e "\n=== Books with AI ===" && \
docker-compose exec -T db mysql -u elibrary_user -pelibrary_pass elibrary \
  -e "SELECT COUNT(*) as ai_books FROM books WHERE has_vector = 1;" && \
echo -e "\n✅ All checks passed!"
```

## 🚀 Next Steps

After verification:
1. Upload more sample PDFs
2. Test with various types of questions
3. Monitor performance and logs
4. Read full documentation in AI_FEATURES.md
5. Customize system prompts if needed

## 📝 Notes

- Keep this checklist for future reference
- Update as you add new features
- Share with team members for onboarding

---

**Date Completed**: _____________

**Verified By**: _____________

**Issues Encountered**: _____________
