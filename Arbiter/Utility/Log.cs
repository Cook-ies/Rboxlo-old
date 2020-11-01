using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Rboxlo.Arbiter.Utility
{
    internal static class Log
    {
        /// <summary>
        /// Log file stream
        /// </summary>
        static StreamWriter log;

        /// <summary>
        /// Log class constructor. Creates a new log file in the current directory
        /// </summary>
        static Log()
        {
            if (Properties.Settings.Default.LogTransactions)
            {
                log = new StreamWriter(String.Format("{0}{1}{2}.txt", Directory.GetCurrentDirectory(), Path.DirectorySeparatorChar, DateTime.Now.ToString("Rboxlo.Arbiter MM_dd_yyyy HH.mm.ss,fff")));
            }
        }

        /// <summary>
        /// Logs an event transaction
        /// </summary>
        /// <param name="message">Message to log</param>
        static internal void Event(string message)
        {
            if (log != null)
            {
                lock (log)
                {
                    log.WriteLine(String.Format("[{0}] - {1}", DateTime.Now.ToString(), message));
                }
            }
        }
    }
}