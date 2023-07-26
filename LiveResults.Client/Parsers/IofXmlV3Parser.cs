using LiveResults.Model;
using Microsoft.VisualBasic.FileIO;
using System;
using System.Collections.Generic;
using System.Globalization;
using System.IO;
using System.Xml;

//  K.Roberts   KR  May 2023    Modified to support position in results for WOC2024. 
//  K.Roberts   KR  May 2023    Added split time controls name definition embedded in comment. 
//  K.Roberts   KR  Jul 2023    Parsed <Nationality> into club and parsed <BibNumber> into bib
//                              N.B. Only added for when parsing results; TODO for parsing start lists 

namespace LiveResults.Client.Parsers
{
    internal class IofXmlV3Parser
    {
        static readonly Dictionary<string, string> m_suppressedIDCalculationErrors = new Dictionary<string, string>();

        public static Runner[] ParseXmlData(XmlDocument xmlDoc, LogMessageDelegate logit, bool deleteFile, LiveResults.Client.Parsers.IofXmlParser.GetIdDelegate getIdFunc, bool readRadioControls, out RadioControl[] radioControls)
        {
            var nsMgr = new XmlNamespaceManager(xmlDoc.NameTable);
            nsMgr.AddNamespace("iof", "http://www.orienteering.org/datastandard/3.0");
            var runners = new List<Runner>();
            var t_radioControls = readRadioControls ? new List<RadioControl>() : null;

            #region parseStartlist
            foreach (XmlNode classStartNode in xmlDoc.GetElementsByTagName("ClassStart"))
            {
                XmlNode classNode = classStartNode.SelectSingleNode("iof:Class", nsMgr);
                if (classNode == null)
                    continue;
                XmlNode classNameNode = classNode.SelectSingleNode("iof:Name", nsMgr);
                if (classNameNode == null)
                    continue;

                string className = classNameNode.InnerText;
                /*Relay*/
                var teamStartNodes = classStartNode.SelectNodes("iof:TeamStart", nsMgr);
                if (teamStartNodes != null)
                {
                    foreach (XmlNode teamStartNode in teamStartNodes)
                    {
                        var teamNameNode = teamStartNode.SelectSingleNode("iof:Name", nsMgr);
                        if (teamNameNode == null)
                            continue;
                        string teamName = teamNameNode.InnerText;

                        var teamMemberStartNodes = teamStartNode.SelectNodes("iof:TeamMemberStart", nsMgr);
                        if (teamMemberStartNodes != null)
                        {
                            foreach (XmlNode teamMemberStartNode in teamMemberStartNodes)
                            {
                                string name = GetNameForTeamMember(nsMgr, teamMemberStartNode);
                                var legNode = teamMemberStartNode.SelectSingleNode("iof:Start/iof:Leg", nsMgr);

                                if (legNode != null)
                                {
                                    string leg = legNode.InnerText;

                                    var startTimeNode = teamMemberStartNode.SelectSingleNode("iof:Start/iof:StartTime", nsMgr);
                                    string starttime = "";
                                    if (startTimeNode != null)
                                        starttime = startTimeNode.InnerText;

                                    var runner = new Runner(-1, name, teamName, className + "-" + leg);

                                    if (!string.IsNullOrEmpty(starttime))
                                    {
                                        int istarttime = ParseTime(starttime);
                                        runner.SetStartTime(istarttime);
                                    }
                                    else
                                    {
                                        runner.SetResult(-9, 9, 0);             // KR: add position
                                    }

                                    runners.Add(runner);
                                }
                            }
                        }
                    }
                }

                /*Individual*/
                var personNodes = classStartNode.SelectNodes("iof:PersonStart", nsMgr);
                if (personNodes != null)
                {
                    foreach (XmlNode personNode in personNodes)
                    {
                        string familyname;
                        string givenname;
                        string club;
                        if (!ParseNameClubAndId(personNode, nsMgr, out familyname, out givenname, out club))
                            continue;

                        var startTimeNode = personNode.SelectSingleNode("iof:Start/iof:StartTime", nsMgr);


                        if (startTimeNode == null)
                            continue;
                        string starttime = startTimeNode.InnerText;

                        var runner = new Runner(-1, givenname + " " + familyname, club, className);

                        if (!string.IsNullOrEmpty(starttime))
                        {
                            int istarttime = ParseTime(starttime);
                            runner.SetStartTime(istarttime);
                        }

                        runners.Add(runner);
                    }
                }
            }
            #endregion
            foreach (XmlNode classResultNode in xmlDoc.GetElementsByTagName("ClassResult"))
            {
                XmlNode classNode = classResultNode.SelectSingleNode("iof:Class", nsMgr);
                if (classNode == null)
                    continue;

                XmlNode classNameNode = classNode.SelectSingleNode("iof:Name", nsMgr);
                if (classNameNode == null)
                    continue;

                string className = classNameNode.InnerText;

                /*Read splitcontrols-extension*/
                if (readRadioControls)
                {
                    CheckReadRadioDefinitionFromComment(t_radioControls, classNode, className);

                    var legNodes = classResultNode.SelectNodes("iof:Class/iof:Leg", nsMgr);
                    if (legNodes != null)
                    {
                        for (int leg = 0; leg < legNodes.Count; leg++)
                        {
                            var legNode = legNodes[leg];
                            CheckReadRadioDefinitionFromComment(t_radioControls, legNode, className + "-" + (leg + 1));
                        }
                    }
                }


                var teamResultNodes = classResultNode.SelectNodes("iof:TeamResult", nsMgr);
                if (teamResultNodes != null)
                {
                    foreach (XmlNode teamResultNode in teamResultNodes)
                    {
                        var teamNameNode = teamResultNode.SelectSingleNode("iof:Name", nsMgr);
                        if (teamNameNode == null)
                            continue;
                        string teamName = teamNameNode.InnerText;

                        var teamMemberResultNodes = teamResultNode.SelectNodes("iof:TeamMemberResult", nsMgr);
                        if (teamMemberResultNodes != null)
                        {
                            foreach (XmlNode teamMemberResult in teamMemberResultNodes)
                            {
                                string name = GetNameForTeamMember(nsMgr, teamMemberResult);
                                var legNode = teamMemberResult.SelectSingleNode("iof:Result/iof:Leg", nsMgr);

                                if (legNode != null)
                                {
                                    string leg = legNode.InnerText;

                                    string bib = null;                                                                          // KR
                                    var bibNumberNode = teamMemberResult.SelectSingleNode("iof:Result/iof:BibNumber", nsMgr);   // KR
                                    if (bibNumberNode != null) bib = bibNumberNode.InnerText;                                   // KR

                                    var runner = new Runner(-1, name, teamName, className + "-" + leg, bib: bib);               // KR - added bib

                                    var competitorStatusNode = teamMemberResult.SelectSingleNode("iof:Result/iof:OverallResult/iof:Status", nsMgr);
                                    var resultTimeNode = teamMemberResult.SelectSingleNode("iof:Result/iof:OverallResult/iof:Time", nsMgr);
                                    var startTimeNode = teamMemberResult.SelectSingleNode("iof:Result/iof:StartTime", nsMgr);
                                    var positionNode = teamMemberResult.SelectSingleNode("iof:Result/iof:OverallResult/iof:Position", nsMgr);   // KR: add position

                                    string status = "notActivated";
                                    if (competitorStatusNode != null)
                                        status = competitorStatusNode.InnerText;

                                    if (status.ToLower() == "notcompeting" || status.ToLower() == "cancelled")
                                    {
                                        //Does not compete, exclude
                                        continue;
                                    }

                                    string time;
                                    ParseResult(runner, resultTimeNode, startTimeNode, positionNode, status, out time);     // KR: add position

                                    XmlNodeList splittimes = teamMemberResult.SelectNodes("iof:Result/iof:SplitTime", nsMgr);
                                    ParseSplitTimes(nsMgr, runner, time, splittimes);

                                    runners.Add(runner);
                                }
                            }
                        }
                    }
                }

                var personNodes = classResultNode.SelectNodes("iof:PersonResult", nsMgr);
                if (personNodes != null)
                {
                    foreach (XmlNode personNode in personNodes)
                    {
                        string familyname;
                        string givenname;
                        string club;
                        if (!ParseNameClubAndId(personNode, nsMgr, out familyname, out givenname, out club)) continue;

                        string bib = null;                                                                      // KR
                        var bibNumberNode = personNode.SelectSingleNode("iof:Result/iof:BibNumber", nsMgr);     // KR
                        if (bibNumberNode != null) bib = bibNumberNode.InnerText;                               // KR

                        var runner = new Runner(-1, givenname + " " + familyname, club, className, bib: bib);   // KR - added bib 

                        var competitorStatusNode = personNode.SelectSingleNode("iof:Result/iof:Status", nsMgr);
                        var resultTimeNode = personNode.SelectSingleNode("iof:Result/iof:Time", nsMgr);
                        var startTimeNode = personNode.SelectSingleNode("iof:Result/iof:StartTime", nsMgr);
                        var positionNode = personNode.SelectSingleNode("iof:Result/iof:Position", nsMgr);   // KR: add position
                        if (competitorStatusNode == null)
                            continue;

                        string status = competitorStatusNode.InnerText;

                        if (status.ToLower() == "notcompeting" || status.ToLower() == "cancelled")
                        {
                            //Does not compete, exclude
                            continue;
                        }

                        string time;
                        ParseResult(runner, resultTimeNode, startTimeNode, positionNode, status, out time);     // KR: add position

                        XmlNodeList splittimes = personNode.SelectNodes("iof:Result/iof:SplitTime", nsMgr);
                        ParseSplitTimes(nsMgr, runner, time, splittimes);

                        runners.Add(runner);
                    }
                }
            }

            radioControls = (t_radioControls != null && t_radioControls.Count > 0) ? t_radioControls.ToArray() : null;
            return runners.ToArray();
        }

        private static string GetNameForTeamMember(XmlNamespaceManager nsMgr, XmlNode sourceNode)
        {
            string name = "";
            var runnerFamilyNameNode = sourceNode.SelectSingleNode("iof:Person/iof:Name/iof:Family", nsMgr);
            var runnerGivenNameNode = sourceNode.SelectSingleNode("iof:Person/iof:Name/iof:Given", nsMgr);
            if (runnerGivenNameNode != null)
            {
                name = runnerGivenNameNode.InnerText;
            }
            if (runnerFamilyNameNode != null)
            {
                name = name + (!string.IsNullOrEmpty(name) ? " " : "") + runnerFamilyNameNode.InnerText;
            }

            if (string.IsNullOrEmpty(name))
            {
                name = "n.n";
            }
            return name;
        }

        private static void CheckReadRadioDefinitionFromComment(List<RadioControl> t_radioControls, XmlNode nodeToCheckForComment, string className)
        {
            // extract split time control codes from a comment of the form: <!-- SplitTimeControls: 34,61,48,40,39,999 -->

            if (nodeToCheckForComment.FirstChild is XmlComment)
            {
                var commentNode = nodeToCheckForComment.FirstChild as XmlComment;
                if (commentNode != null)
                {
                    var comment = commentNode.InnerText;
                    if (!string.IsNullOrEmpty(comment) && comment.ToLower().IndexOf("splittimecontrols:") >= 0)
                    {
                        var parts = comment.Split(':');
                        if (string.Compare(parts[0].Trim(), "splittimecontrols", StringComparison.InvariantCultureIgnoreCase) == 0)
                        {
                            var splitControls = parts[1].Trim().Split(',');
                            Dictionary<int, int> radioCnt = new Dictionary<int, int>();
                            var splitControlsNames = CheckReadRadioDefinitionFromAdditionalComment(commentNode);            // KR
                            for (int i = 0; i < splitControls.Length; i++)
                            {
                                if (splitControls[i].Trim() != "999")
                                {
                                    int code = Convert.ToInt32(splitControls[i].Trim());
                                    if (!radioCnt.ContainsKey(code))
                                        radioCnt.Add(code, 0);
                                    radioCnt[code]++;
                                    t_radioControls.Add(new RadioControl
                                    {
                                        Order = i,
                                        ClassName = className,
                                        Code = radioCnt[code] * 1000 + code,
                                        ControlName = (i < splitControlsNames.Count && !string.IsNullOrWhiteSpace(splitControlsNames[i])
                                                        ? splitControlsNames[i] : $"({code})")                              // KR
                                    });
                                }
                            }
                        }
                    }
                }
            }
        }

        // KR:  added following method to extract split time control names from a second comment 
        //      of the form: <!-- SplitTimeControlsNames: "TV-1","TV-2","Prewarning","TV-3","Final","Finish" -->

        private static List<string> CheckReadRadioDefinitionFromAdditionalComment(XmlNode commentNode)
        {
            var splitControlsNames = new List<string>();
            var additionalCommentNode = commentNode.NextSibling as XmlComment;
            if (additionalCommentNode != null)
            {
                var additionalComment = additionalCommentNode.InnerText;
                if (!string.IsNullOrEmpty(additionalComment) && additionalComment.ToLower().IndexOf("splittimecontrolsnames:") >= 0)
                {
                    var parts = additionalComment.Split(':');
                    if (string.Compare(parts[0].Trim(), "splittimecontrolsnames", StringComparison.InvariantCultureIgnoreCase) == 0)
                    {
                        using (var csvParser = new TextFieldParser(new StringReader(parts[1].Trim())))
                        {
                            csvParser.HasFieldsEnclosedInQuotes = true;
                            csvParser.SetDelimiters(",");
                            var fields = csvParser.ReadFields();
                            csvParser.Close();
                            foreach (var field in fields) splitControlsNames.Add(field.Trim());
                        }
                    }
                }
            }
            return splitControlsNames;
        }

        private static void ParseResult(Runner runner, XmlNode resultTimeNode, XmlNode startTimeNode, XmlNode positionNode, string status, out string time)     // KR: add position
        {
            int itime;
            int istatus;
            time = "";
            if (resultTimeNode != null)
                time = resultTimeNode.InnerText;

            FixKraemerTimeFormat(ref time);

            string starttime = "";
            if (startTimeNode != null)
                starttime = startTimeNode.InnerText;

            if (!string.IsNullOrEmpty(starttime))
            {
                int istarttime = ParseTime(starttime);
                runner.SetStartTime(istarttime);
            }

            int iposition = 0;                                  // KR: add position
            string position = "";                               //
            if (positionNode != null)                           //
                position = positionNode.InnerText;              //
                                                                //
            if (!string.IsNullOrEmpty(position))                //
            {                                                   //    
                _ = int.TryParse(position, out iposition);      //
            }                                                   //

            itime = string.IsNullOrEmpty(time) ? -10 : (int)(Convert.ToDouble(time, CultureInfo.InvariantCulture) * 100);//ParseTime(time);
            istatus = 10;



            switch (status.ToLower())
            {
                case "missingpunch":
                    istatus = 3;
                    break;

                case "disqualified":
                    istatus = 4;
                    break;
                case "didnotfinish":
                    istatus = 3;
                    itime = -3;
                    break;
                case "didnotstart":
                    istatus = 1;
                    itime = -3;
                    break;
                case "overtime":
                    istatus = 5;
                    break;
                case "ok":
                    istatus = 0;
                    break;
            }

            runner.SetResult(itime, istatus, iposition);            // KR: add position
        }

        private static void FixKraemerTimeFormat(ref string time)
        {
            if (!string.IsNullOrEmpty(time) && time.Contains(","))
            {
                //Hack for krämer-software exporting decimal numbers with local numberformat for which can be , for decimal separator
                time = time.Replace(",", ".");
            }
        }

        private static void ParseSplitTimes(XmlNamespaceManager nsMgr, Runner runner, string time, XmlNodeList splittimes)
        {
            if (splittimes != null)
            {
                var lsplitCodes = new List<int>();
                var lsplitTimes = new List<int>();
                foreach (XmlNode splitNode in splittimes)
                {
                    XmlNode splitcode = splitNode.SelectSingleNode("iof:ControlCode", nsMgr);
                    XmlNode splittime = splitNode.SelectSingleNode("iof:Time", nsMgr);
                    if (splittime == null || splitcode == null)
                        continue;

                    int iSplitcode;
                    string sSplittime = splittime.InnerText;

                    bool parseOK = int.TryParse(splitcode.InnerText, out iSplitcode);
                    bool isFinishPunch = splitcode.InnerText.StartsWith("F", StringComparison.InvariantCultureIgnoreCase) || iSplitcode == 999;
                    if ((parseOK || isFinishPunch) && sSplittime.Length > 0)
                    {
                        if (isFinishPunch)
                        {
                            if ((runner.Status == 0 && runner.Time == -1) || (runner.Status == 10 && runner.Time == -9))
                            {
                                //Målstämpling
                                if (!string.IsNullOrEmpty(time))
                                {
                                    FixKraemerTimeFormat(ref time);
                                    var itime = (int)(Convert.ToDouble(time, CultureInfo.InvariantCulture) * 100);
                                    runner.SetResult(itime, 0, 0);              // KR: add position
                                }
                            }
                        }
                        else
                        {
                            iSplitcode += 1000;
                            while (lsplitCodes.Contains(iSplitcode))
                            {
                                iSplitcode += 1000;
                            }

                            if (!string.IsNullOrEmpty(sSplittime))
                            {
                                FixKraemerTimeFormat(ref sSplittime);
                                int iSplittime = (int)(Convert.ToDouble(sSplittime, CultureInfo.InvariantCulture) * 100);
                                lsplitCodes.Add(iSplitcode);
                                lsplitTimes.Add(iSplittime);

                                runner.SetSplitTime(iSplitcode, iSplittime);
                            }
                        }
                    }
                }
            }
        }

        private static bool ParseNameClubAndId(XmlNode personResultNode, XmlNamespaceManager nsMgr, out string familyname, out string givenname,
           out string club)
        {

            club = null;
            familyname = null;
            givenname = null;


            XmlNode personNameNode = personResultNode.SelectSingleNode("iof:Person/iof:Name", nsMgr);
            if (personNameNode == null)
            {
                return false;
            }

            var familyNameNode = personNameNode.SelectSingleNode("iof:Family", nsMgr);
            var giveNameNode = personNameNode.SelectSingleNode("iof:Given", nsMgr);
            if (familyNameNode == null || giveNameNode == null)
            {
                return false;
            }

            familyname = familyNameNode.InnerText;
            givenname = giveNameNode.InnerText;
            // sourceId = personIdNode.InnerText;

            //var clubNode = personResultNode.SelectSingleNode("iof:Organisation/iof:ShortName",nsMgr); // KR
            var clubNode = personResultNode.SelectSingleNode("iof:Person/iof:Nationality", nsMgr);      // KR use nationality for club
            club = "";
            if (clubNode != null)
            {
                //club = clubNode.InnerText;                                                            // KR
                if (clubNode.Attributes["code"] != null) club = clubNode.Attributes["code"].Value;      // KR                                              // KR
            }
            return true;
        }

        private static int ParseTime(string time)
        {
            int itime = -9;
            if (!string.IsNullOrEmpty(time))
            {
                var dttime = DateTime.Parse(time, System.Globalization.CultureInfo.InvariantCulture);
                itime = (int)(dttime.TimeOfDay.TotalMilliseconds / 10);
            }
            return itime;
        }

    }
}
