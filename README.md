# UbugeniPalace (U-Pal) 🎨

Welcome to **UbugeniPalace**, a digital home for authentic Rwandan craftsmanship. I built this platform to bridge the gap between our incredibly talented local artisans and the rest of the world. It’s more than just a shop; it’s a place where every piece tells a story of tradition, skill, and passion.

## 🚀 What's New? (Latest Upgrades)
I’ve recently overhauled the technical side of the app to make it faster, more reliable, and modern:

*   **Docker Ready:** No more "it works on my machine" issues. You can spin up the entire environment with a single command.
*   **Supabase (PostgreSQL) Integration:** Switched to a robust cloud database for better performance and scaling.
*   **Cloudinary Magic:** All images are now hosted on Cloudinary. This means lightning-fast loading and automatic image optimization.
*   **Clean URLs:** I've stripped away those ugly `.php` extensions. Navigating the site feels much smoother now (e.g., `/pages/artisans` instead of `/pages/artisans.php`).
*   **Graceful Failure:** If you hit a link that isn't ready yet, you'll see a custom "Feature Under Design" page instead of a boring 404 error.

## 🛠️ Tech Stack
*   **Backend:** PHP 8.2 (Running on Apache)
*   **Database:** PostgreSQL (via Supabase)
*   **Storage:** Cloudinary (for all those beautiful craft photos)
*   **Environment:** Docker & Docker Compose
*   **Frontend:** Pure HTML5, CSS3 (Vanilla), and JavaScript.

## 🏃‍♂️ How to get it running
If you want to play with the code locally:

1.  **Clone the repo:**
    ```bash
    git clone https://github.com/ManJoseph/UbugeniPalace.git
    cd UbugeniPalace
    ```

2.  **Setup your environment:**
    Copy `.env.example` to `.env` and fill in your Supabase and Cloudinary keys.
    ```bash
    cp .env.example .env
    ```

3.  **Fire up Docker:**
    ```bash
    docker-compose up -d
    ```
    The app will be live at `http://localhost:8080`.

4.  **Install Dependencies:**
    ```bash
    docker exec -it ubugenipalace_app composer install
    ```

## 📸 Image Management
The app is designed to work with Cloudinary. If you hit your free tier limits, don't worry—I've built in a "kill switch." Just go to `config/config.php` and set `USE_CLOUDINARY` to `false` to fall back to local images.

## 🤝 Let's Work Together!
I’m **Joseph Manizabayo**, the developer behind this project. I love building digital solutions that solve real-world problems and celebrate culture.

**Are you interested in a project like this?**
If you need a custom marketplace, a portfolio, or any web application tailored to your needs, I’m available for hire! 

*   **Email:** [josephmanizabayo7@gmail.com](mailto:josephmanizabayo7@gmail.com)
*   **GitHub:** [@ManJoseph](https://github.com/ManJoseph)

Let’s build something amazing together! 🚀

---
*Created with ❤️ in Rwanda.*
