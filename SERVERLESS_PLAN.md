# Step-by-Step Serverless Deployment Guide (Vercel + Supabase)

Follow these exact steps to get your project live for free.

---

## Part 1: Supabase Setup (The Database)

1.  **Sign Up**: Go to [Supabase.com](https://supabase.com/) and sign up with GitHub or Email.
2.  **New Project**:
    *   Click **"New Project"**.
    *   Name it `lacowe-mis`.
    *   **Database Password**: Create a strong password and **Save it!** You will need it for Vercel.
    *   Region: Choose the one closest to you (e.g., Mumbai, Frankfurt, US East).
    *   Click **"Create new project"**. Wait about 2 minutes for it to provision.
3.  **Run SQL**:
    *   In the left sidebar, click the **"SQL Editor"** icon (looks like `>_`).
    *   Click **"+ New query"**.
    *   Open your local file: `database/schema_postgres.sql`.
    *   Copy the entire content and paste it into the Supabase SQL Editor.
    *   Click **"Run"**. You should see "Success. No rows returned."
4.  **Get Credentials**:
    *   Click the **"Project Settings"** (Gear icon) at the bottom left.
    *   Click **"Database"** under the Settings menu.
    *   Scroll down to **"Connection Info"**. Note these for Vercel:
        *   **Host**: (looks like `db.xxxx.supabase.co`)
        *   **Database Name**: usually `postgres`
        *   **Port**: `5432`
        *   **User**: `postgres`
        *   **Password**: (The one you created in Step 2)

---

## Part 2: Vercel Setup (The Frontend)

1.  **Prepare Files**: Ensure `vercel.json` is in your project root folder.
2.  **Sign Up**: Go to [Vercel.com](https://vercel.com/) and sign up.
3.  **Deploy**:
    *   **Option A (Easiest)**: Push your code to a **GitHub** repository, then click **"Add New"** > **"Project"** in Vercel and import it.
    *   **Option B (CLI)**: Install Vercel CLI (`npm i -g vercel`), open your terminal in the project folder, and type `vercel`.
4.  **Add Environment Variables**:
    *   During the "Configure Project" step in Vercel, expand the **"Environment Variables"** section.
    *   Add the following (copy exactly from Supabase):
        *   `DB_DRIVER` = `pgsql`
        *   `DB_HOST` = (Your Supabase Host)
        *   `DB_NAME` = `postgres`
        *   `DB_USER` = `postgres`
        *   `DB_PASS` = (Your Database Password)
        *   `DB_PORT` = `5432`
    *   Click **"Deploy"**.

---

## Part 3: Go Live!

1.  Vercel will give you a URL like `https://lacowe-mis.vercel.app`.
2.  Open it. If you see the login screen, you are successful!
3.  **Test**: Log in with your admin credentials. Everything should work identically to your local XAMPP setup.

---

## 🆘 Troubleshooting: "Cannot assign requested address"

If you see this error, it means Vercel's server is trying to use **IPv6** to reach Supabase, but it's failing. Here is how to fix it in 2 minutes:

1.  **Switch to the Pooler Method**:
    *   In the **"Connect to your project"** window (the one in your screenshot):
    *   Click the **"Method"** dropdown (usually says "Direct connection").
    *   Change it to **"Pooler"**.
    *   A new box called **"Mode"** will appear—make sure it is set to **"Transaction"**.
2.  **Copy the New Credentials**:
    *   Look at the grey box above. The host will change to something ending in **`.pooler.supabase.com`**.
    *   The port will change to **`6543`**.
3.  **Update Vercel**:
    *   Update `DB_HOST` with this new pooler host.
    *   Update `DB_PORT` to `6543`.
4.  **Redeploy**: Go to Vercel and **Redeploy**.

**Why this works**: Vercel only supports **IPv4** for outbound connections. The "Direct" method uses IPv6, which Vercel doesn't like. The "Pooler" method uses IPv4, which works perfectly.
