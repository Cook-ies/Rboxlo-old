using Newtonsoft.Json.Converters;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Rboxlo.Arbiter.Client.RccService
{
    public class RccServiceProcess
    {
        private static string RccServiceLaunch = "/Console {0} {1} ";
        private static string RccServiceCrashUploader = "/CrashReporter {0} {1} ";

        private Process process;
        private int port;
        public int threads;

        public bool HasExited { get { return process.HasExited; } }
        public DateTime ExpirationTime { get; set; }
        public RccServiceSoap SoapInterface
        {
            get
            {
                RccServiceSoap result = new RccServiceSoap();
                result.Url = String.Format("http://localhost:{0}", port.ToString());
                result.Timeout = (int)Properties.Settings.Default.RccServiceJobTimeout.TotalMilliseconds;
                
                return result;
            }
        }

        public RccServiceProcess(int port)
        {
            this.port = port;
            this.process = new Process();
        }

        public void Start(string exe)
        {
            process.StartInfo = new ProcessStartInfo(exe, String.Format(RccServiceLaunch, Properties.Settings.Default.RccServiceArguments, port));
            process.Start();
        }

        public void StartCrashUploader(string exe)
        {
            process.StartInfo = new ProcessStartInfo(exe, String.Format(RccServiceCrashUploader, Properties.Settings.Default.RccServiceArguments, port));
            process.Start();
        }

        public void Close()
        {
            process.Kill();
        }
    }
}
