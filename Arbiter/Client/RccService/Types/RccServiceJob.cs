using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Rboxlo.Arbiter.Client.RccService
{
    public class RccServiceJob
    {
        public int cores { get; set; }
        public int category { get; set; }
        public string id { get; set; }
        public double expirationInSeconds { get; internal set; }
    }
}
