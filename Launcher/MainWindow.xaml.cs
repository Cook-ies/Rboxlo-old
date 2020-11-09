using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;
using System.Windows.Documents;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Media.Imaging;
using System.Windows.Navigation;
using System.Windows.Shapes;
using System.IO;
using System.Net;
using System.Drawing;
using System.Windows.Interop;
using System.Diagnostics;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System.Security.Cryptography;
using Microsoft.Win32;

namespace Rboxlo.Launcher
{
    /// <summary>
    /// Interaction logic for MainWindow.xaml
    /// </summary>
    public partial class MainWindow : Window
    {
        private static string LocalApplicationData = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
        private static string CurrentWorkingDirectory = Directory.GetCurrentDirectory();
        private static string BaseUrl = "http://rboxlo.loc";
        private WebClient InternetConnection = new WebClient();

        public MainWindow()
        {
            InitializeComponent();
            ConnectRboxlo();
        }
        
        /// <summary>
        /// Adds the Rboxlo program to the users Registry, thereby creating an option to uninstall Rboxlo through the Control Panel. 
        /// </summary>
        /// <param name="icon">Sets the icon of the program in the Control Panel. Path to an ".ico" file</param>
        /// <param name="location">The path/folder where the program is being installed</param>
        /// <param name="uninstall">Command line arguments to uninstall the program</param>
        private void AddToRegistry(string icon, string location, string uninstall)
        {
            DateTime now = DateTime.Today;
            int install = Convert.ToInt32(now.ToString("yyyymmdd"));

            RegistryKey key = Registry.CurrentUser.CreateSubKey(@"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\Rboxlo");
            key.SetValue( "DisplayIcon",     icon,      RegistryValueKind.String );
            key.SetValue( "DisplayName",     "Rboxlo",  RegistryValueKind.String );
            key.SetValue( "InstallDate",     install,   RegistryValueKind.String );
            key.SetValue( "InstallLocation", location,  RegistryValueKind.String );
            key.SetValue( "NoModify",        1,         RegistryValueKind.DWord  );
            key.SetValue( "NoRepair",        1,         RegistryValueKind.DWord  );
            key.SetValue( "Publisher",       "Rboxlo",  RegistryValueKind.String );
            key.SetValue( "UninstallString", uninstall, RegistryValueKind.String );
            key.SetValue( "URLInfoAbout",    BaseUrl,   RegistryValueKind.String );
            key.Close();
        }

        /// <summary>
        /// Gets a sha256 hash of a file
        /// </summary>
        /// <param name="fileName">Path to the file</param>
        /// <returns></returns>
        private string GetSha256Hash(string fileName)
        {
            FileStream filestream;
            SHA256 mySHA256 = SHA256Managed.Create();
            filestream = new FileStream(fileName, FileMode.Open);
            filestream.Position = 0;
            byte[] hashValue = mySHA256.ComputeHash(filestream);
            filestream.Close();

            return BitConverter.ToString(hashValue).Replace("-", String.Empty).ToLower();
        }

        /// <summary>
        /// Fails the launch/setup process by changing the icon to an error and hiding the progress bar. However, this does *not* halt the program, and only displays an error message
        /// </summary>
        /// <param name="message">Error message to display</param>
        private void FailSetup(string message)
        {
            StatusText.Content = message;
            StatusProgressBar.Visibility = Visibility.Hidden;
            StatusImage.Source = Imaging.CreateBitmapSourceFromHIcon(SystemIcons.Error.Handle, Int32Rect.Empty, BitmapSizeOptions.FromEmptyOptions());
        }

        /// <summary>
        /// Part of the shared setup and launching process. Attempts to connect to the website. Determines if we have an internet connection, and if the website is up.
        /// </summary>
        private void ConnectRboxlo()
        {
            bool succeeded = false;
            StatusText.Content = "Connecting to Rboxlo...";
            ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;

            // Attempt connection
            try
            {
                var output = InternetConnection.DownloadString(BaseUrl + "/api/setup/ok");
                succeeded = (bool)JObject.Parse(output)["success"];
            }
            catch
            {
                FailSetup("Failed to connect to Rboxlo.");
            }

            if (succeeded)
            {
                // Move on to the next stage.
                InitializeRboxlo();
            }
        }

        /// <summary>
        /// Second step of shared setup/launching process. This is where the setup/launching process splits
        /// </summary>
        private void InitializeRboxlo()
        {
            // See our directory
            if (CurrentWorkingDirectory != LocalApplicationData + @"\Rboxlo")
            {
                // If we aren't in LocalAppData, assume that we are installing a new copy
                // Other than that, there is no "proper" way of downloading a new launcher.
                // I could do it like Roblox does it, where the case is a new launcher for each version,
                // but I'd have to mess with the registry and url protocols 24/7.

                bool succeeded = true;

                if (Directory.Exists(LocalApplicationData + @"\Rboxlo"))
                {
                    Directory.Delete(LocalApplicationData + @"\Rboxlo", true);
                }
                Directory.CreateDirectory(LocalApplicationData + @"\Rboxlo");

                JObject details = JObject.Parse(InternetConnection.DownloadString(BaseUrl + "/api/setup/info"));
                string launcherUrl = BaseUrl + "/api/setup/files/launcher/" + (string)details["launcher"];
                InternetConnection.DownloadFile(launcherUrl, LocalApplicationData + @"\Rboxlo\RboxloLauncher.exe");

                // Verify that we got the valid launcher
                if ((string)details["launcher"] != GetSha256Hash(LocalApplicationData + @"\Rboxlo\RboxloLauncher.exe"))
                {
                    FailSetup("Failed to verify integrity.");
                    succeeded = false;
                }

                if (succeeded)
                {
                    // Start that process up with our arguments
                    StatusText.Content = "Initializing Rboxlo...";

                    ProcessStartInfo launcher = new ProcessStartInfo(LocalApplicationData + @"\Rboxlo\RboxloLauncher.exe");
                    launcher.UseShellExecute = true;
                    launcher.Arguments = String.Join(" ", GlobalVars.Arguments);

                    Process.Start(launcher);
                    Application.Current.Shutdown();
                }
            }

            // Lets check our arguments.
            if (GlobalVars.Arguments.Length > 0)
            {

            }
        }

        /// <summary>
        /// Event handler for CancelButton
        /// </summary>
        private void CancelButtonClick(object sender, RoutedEventArgs e)
        {
            Application.Current.Shutdown();
        }
    }
}
