using System;
using System.IO;
using System.Xml;

namespace Rboxlo.Arbiter.Utility
{
    public static class Functions
    {
        /// <summary>
        /// Current base URL
        /// </summary>
        private static string baseUrl = String.Empty;

        /// <summary>
        /// Fetches the BaseUrl from the provided AppSettings
        /// </summary>
        /// <returns>Applications BaseUrl</returns>
        public static string GetBaseUrl()
        {
            if (baseUrl.Length == 0)
            {
                string settingsFilePath = String.Format("{0}{1}{2}", Directory.GetCurrentDirectory(), Path.DirectorySeparatorChar, Properties.Settings.Default.AppSettingsPath);

                XmlDocument xml = new XmlDocument();
                xml.Load(settingsFilePath);

                baseUrl = xml.GetElementsByTagName("BaseUrl")[0].InnerText;
            }

            return baseUrl;
        }
    }
}
