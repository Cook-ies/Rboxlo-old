using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Configuration;
using System.Runtime.Remoting.Channels;
using System.Net;
using System.IO;
using Newtonsoft.Json.Linq;

namespace Rboxlo.Arbiter.Utility
{
    public static class ClientSettings
    {
        /// <summary>
        /// Builds a ClientSettings URL
        /// </summary>
        /// <param name="baseUrl">Domain to fetch from</param>
        /// <param name="group">Settings group</param>
        /// <returns>ClientSettings URL with provided settings group and base URL</returns>
        private static string BuildSettingsUrl(string baseUrl, string group)
        {
            UriBuilder builder = new UriBuilder(baseUrl);
            string host = builder.Host;

            host = host.Replace("www.", "");
            return String.Format("https://clientsettings.api.{0}/Setting/QuietGet/{1}/?apiKey={2}", host, group, Properties.Settings.Default.ApiKey);
        }

        /// <summary>
        /// Fetches the ClientSettings of a given group
        /// </summary>
        /// <param name="group">Settings group</param>
        /// <returns>Client settings JSON data</returns>
        public static string Fetch(string group)
        {
            string baseUrl = Functions.GetBaseUrl();
            if (baseUrl.Length == 0)
            {
                // You didn't set BaseURL before loading settings!
                Console.WriteLine("Failed to fetch BaseUrl");
                return null;
            }

            string data = String.Empty;
            try
            {
                string url = BuildSettingsUrl(baseUrl, group);
                HttpWebRequest request = (HttpWebRequest)WebRequest.Create(url);
                HttpWebResponse response = (HttpWebResponse)request.GetResponse();

                if (response.ContentLength > 0)
                {
                    StreamReader readStream = new StreamReader(response.GetResponseStream(), System.Text.Encoding.GetEncoding("utf-8"));
                    data = readStream.ReadToEnd();
                }

                response.Close();
            }
            catch (Exception e)
            {
                throw e;
            }

            return data;
        }

        public static JObject Fetch()
        {
            string data = Fetch("Arbiter");
            JObject settings = null;

            try
            {
                settings = JObject.Parse(data);
            }
            catch (Exception e)
            {
                throw e;
            }

            return settings;
        }
    }
}
