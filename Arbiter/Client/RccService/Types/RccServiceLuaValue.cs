using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Rboxlo.Arbiter.Client.RccService
{
    public class RccServiceLuaValue
    {
        public enum Types
        {
            LUA_TBOOLEAN,
            LUA_TNUMBER,
            LUA_TSTRING,
            LUA_TNIL,
            LUA_TTABLE
        };
        public Types LuaType { get; set; }

        public string Value { get; set; }
        public RccServiceLuaValue[] Table { get; set; }
    }
}
