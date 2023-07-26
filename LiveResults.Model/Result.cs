using System.Collections.Generic;

//  K.Roberts   KR  May 2023    Modified to support position in results for WOC2024. 

namespace LiveResults.Model
{
    public delegate void ResultDelegate(Result newResult);
    public delegate void RadioControlDelegate(string controlName, int controlCode, string className, int order);

    public class Result
    {
        public int ID { get; set; }
        public string RunnerName { get; set; }
        public string RunnerClub { get; set; }
        public string Class { get; set; }
        public int StartTime { get; set; }
        public int Time { get; set; }
        public int Position { get; set; }
        public int Status { get; set; }

        public string bib { get; set; }
        public List<ResultStruct> SplitTimes { get; set; }
    }

    public class OverallResult : Result
    {
        public int OverallTime { get; set; }
        public int OverallStatus { get; set; }
    }

    public class RelayResult : OverallResult
    {
        public int LegNumber { get; set; }
    }
}
