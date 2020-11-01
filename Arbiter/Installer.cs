using System.ComponentModel;

namespace Rboxlo.Arbiter
{
    [RunInstaller(true)]
    class Installer
    {
        public string ServiceName
        {
            get { return "Rboxlo.Arbiter";  }
        }

        public string DisplayName
        {
            get { return "Rboxlo Arbiter"; }
        }

        public string Description
        {
            get { return "Manages a handful of processes running RCCService, or GameServer processes (e.g Player/Studio.)"; }
        }
    }
}
