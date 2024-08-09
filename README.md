## Description

This is a bot which is passing registration stages

## Installation

Just Run
- docker-compose up -d --build

## Configuration

Before running the command, you need to configure some env variables:

- REMOTE_BASE_URL=https://challenge.blackscale.media
- REMOTE_DOMAIN=challenge.blackscale.media

- MAIL_PARSER_API=https://api.guerrillamail.com
- MAIL_PARSER_DOMAIN=@sharklasers.com

- NEXT_CAPTCHA_SOLVER_API=https://api.nextcaptcha.com
- NEXT_CAPTCHA_SOLVER_API_KEY=your_api_key

Once you have set up your .env file, run the register-bot command, which will go through the 3 stages of the https://challenge.blackscale.media website:

``php artisan app:register-bot``
