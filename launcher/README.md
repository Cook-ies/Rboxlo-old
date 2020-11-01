# Rboxlo.Launcher
Handles all launching of the *client*, meaning both Studio and Player Rboxlo applications. This acts as a middleman to handle communication properly from the website to the client. There is no direct website -> client communication, making integration with new clients much more easier.

# Launching process
This explains the Rboxlo.Launchers process step by step in a human readable format that isn't computer language. Take "FALSE" as "we aren't" or "no" and "TRUE" as "we are" or "yes" if you do not understand the terms. "Website" is used throughout to define the BaseUrl set in the launcher upon compilation. The launcher goes through this exact process each time it is opened.

1. Are we connected to the internet (can we retrieve a successful server response from the website? [`/api/setup/ok`])
    - **TRUE**:  Continue.
    - **FALSE**: Generate an error message and halt the launching process.
2. Are we being launched from `%localappdata%\Rboxlo`?
    - **TRUE**: We are. Continue.
    - **FALSE**: We fetch the launcher manifest from the website ([`/api/setup/info`]), download the file hash given and put our resulting file as `%localappdata%\Rboxlo\Rboxlo.Launcher.exe`. Then, let us confirm the integrity by SHA256 hashing our downloaded file and comparing the hash with the hash that we receieved from the server. Did the hash comparison succeed?
        - **TRUE**: We then create an entry in the registry so that Rboxlo can easily be uninstalled from the Control Panel, or Settings app. Continue.
        - **FALSE**: Bad file returned from server, stop and delete ourselves.
3. Do we have arguments?
    - **TRUE**: Continue.
    - **FALSE**: Open up the website's games page, and close the process.
4. Do we have URI arguments?
    - **TRUE**: Continue.
    - **FALSE**: Do we have "`-uninstall`" as a parameter?
        - **TRUE**: Delete all the files in `%localappdata%\Rboxlo` and use some programming magic to delete ourself (`Rboxlo.Launcher.exe`).
        - **FALSE**: This means that we were most likely not launched in any realistic scenario. Exit the process.
5. Are our passed URI arguments Base64 encoded?
    - **TRUE**: Continue.
    - **FALSE**: This means that we were most likely not launched from the website. Exit the process.
6. Split the arguments into an array. Did we successfully split the arguments into 4 parts, the version as an integer, the game type (only "studio" or "client" is possible, as string literals), and a Unix timestamp?
    - **TRUE**: Continue.
    - **FALSE**: This means that we were **most definitely** not launched from the website. Exit the process.
7. Let us take our version integer, and fetch a file manifest from the website. Did we get one?
    - **TRUE**: Continue.
    - **FALSE**: Did we get a "password_required" field?
        - **TRUE**: This version is password-protected (essentially meaning that it is private.) Display a password form prompt, and let the user enter their password. Submit the password, and get a website response. Does the website report a successful password?
            - **TRUE**: Continue.
            - **FALSE**: Incorrect password. Display an error message, and halt.
        - **FALSE**: This version does not exist. Display an error message, and halt.
8. Let us take our version integer, and check if the `%localappdata%\Rboxlo\{version}` folder exists. Does it?
    - **TRUE**: We have it installed. Let us compare the "core files" SHA256 hashes (specified in the file manifest) with the "core file" SHA256 hashes in our folder path. If the comparison fails, we download the files from the website (on a per-hash basis) and replace them. This also helps if the files are missing altogether.
    - **FALSE**: This means we don't have it installed. We create that folder path, and install all the files given from our manifest and mark the "read-only" attribute as true (this can be un-set for client side mods, such as for example a cursor texture change.) We generate a SHA256 hash of all the files there and compare it with the manifest. If we fail anywhere, assume that we have failed to verify the content integrity of files and that we are probably on an insecure connection or the website has been hijacked. We display an error message akin to if we fail to connect to the website that halts the launching process.

TODO: the rest of this