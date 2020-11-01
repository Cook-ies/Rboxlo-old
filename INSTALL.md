# Installation
So, you want to install Rboxlo. Well, you're in luck! Rboxlo is a fairly small piece of software that should take no time at all to set up.

This guide assumes you have the following tools already installed. If you do not have them installed, it is recommended you go get them.
    - Visual Studio 2019
    - Docker (if on Windows, use Docker Desktop)
    - Git
    - Some sort of code editor (recommended is Visual Studio Code, but you can use Notepad++ or Notepad even.)
    - Some sort of terminal (if on Windows, just use Command Prompt)

1. Clone this repository using Git by running this command in your terminal: `git clone https://github.com/lighterlightbulb/Rboxlo`
2. Change your current directory to the cloned repository (usually `cd Rboxlo`).
3. Now, you will want to edit your environment variables. Copy `.env.sample`, and rename the copy to `.env`. Change the values as you please (each value is documented.)
4. Run the PowerShell script (`generate-api-keys.ps1`) by right clicking and running "Run with PowerShell" if on Windows.
5. The server side of Rboxlo is set-up. Run `docker-compose up --build`, and go to localhost to see it in action.
6. Open Rboxlo.sln.
7. Build.
8. The client side of Rboxlo has been built. Arbiter is to be run on your servers hosting Rboxlo, and you distribute the Launcher to those seeking to play the game.e

# Troubleshooting
- Rboxlo is set up with `rboxlo.local` everywhere as its default domain. Add all the domains in `Website/NGINX/Domains.conf` in the format `127.0.0.1 {domain}.rboxlo.local` to your hosts file to use Rboxlo to its full potential.
- If you are in a debugging environment, chances are that you can't access Rboxlo via a secure method such as HTTPS on localhost. This will result in the session cookie not being set, and tons of things breaking. In `Website/PHP.ini`, comment out line 6 (add a pound sign [`#`] at the beginning.)