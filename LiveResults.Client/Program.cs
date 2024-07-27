using System;
using System.Collections.Generic;
using System.Configuration;
using System.Windows.Forms;

namespace LiveResults.Client
{
    // K.Roberts    KR      Added bypass of new competition form and lauch the iofxml import form directly

    static class Program
    {
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            bool m_iofxmlimport = false;        // KR

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            var iofxmlimport = ConfigurationManager.AppSettings["iofxmlimport"];                                // KR
            if (!string.IsNullOrWhiteSpace(iofxmlimport)) _ = bool.TryParse(iofxmlimport, out m_iofxmlimport);  // KR

            /*
            //using (var sirapInterface = new SirapInterface())
            {
                //  sirapInterface.Start();
                Application.Run(new FrmNewCompetition());
                //sirapInterface.Stop();
            }
            */

            if (m_iofxmlimport)
            {
                Application.Run(new OEForm());
            }
            else
            {
                Application.Run(new FrmNewCompetition());
            }

        }
    }
}