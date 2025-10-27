# SmartDev Academic LMS - Deployment Guide

## 🚀 Railway Deployment (Recommended)

### Prerequisites
- GitHub account
- Railway account (signup with GitHub)

### Step-by-Step Deployment

1. **Push to GitHub** ✅ (Already done)
   ```bash
   git add .
   git commit -m "Add Railway deployment config"
   git push origin main
   ```

2. **Deploy on Railway**
   - Go to [railway.app](https://railway.app)
   - Click "Start a New Project"
   - Select "Deploy from GitHub repo"
   - Choose `Project_lmsRPL3` repository
   - Railway will auto-detect Laravel and deploy

3. **Environment Setup**
   - In Railway dashboard, go to Variables tab
   - Copy contents from `env.production` file
   - Set `APP_URL` to your Railway domain
   - Generate `APP_KEY` (Railway will do this automatically)

4. **Database Setup**
   - SQLite will be created automatically
   - Migrations will run during deployment
   - Database file persists in Railway volumes

### Alternative Platforms

#### Render.com
- Connect GitHub repo
- Use `railway-build.sh` as build command
- Use `php artisan serve --host=0.0.0.0 --port=$PORT` as start command

#### Fly.io
```bash
fly launch
fly deploy
```

#### Vercel (Frontend only)
- For API-only deployment
- Use serverless functions

### Production Checklist
- [ ] Environment variables set
- [ ] APP_DEBUG=false
- [ ] Database migrations run
- [ ] SSL certificate (auto on Railway)
- [ ] Custom domain (optional)
- [ ] File uploads working
- [ ] API endpoints tested

### Monitoring
- Railway provides built-in metrics
- Check logs in Railway dashboard
- Set up error tracking (optional)

### Costs
- Railway: $5/month after free credits
- Render: Free tier with limitations
- Fly.io: Pay-as-you-go

## 🎯 Quick Railway Setup

1. Visit: https://railway.app
2. Connect GitHub
3. Import `Project_lmsRPL3`
4. Wait for auto-deploy
5. Your LMS is live! 🎉