In order to describe all possible character in text to match, we use user-defined class, Alphabet to specify the group of all character in an encoding.
# Alphabet Class
For clone the full implementation of Alphabet class, please refer my github: [Github](https://github.com/ddayzzz/Algorithms-In-CSharp/blob/master/Algorithms%20In%20CSharp/StringDemo/Alphabet.cs). Some interfaces：
Method|Description|Method|Description
-|-|-|-
Alphabet()|default constructor, the default range of a enocidng is [0, 256)|Alphabet(int R)|constructor, the default range of a enocidng is [0,R)
Alphabet(string s)|According to string s with no duplicates, generating a new encoding range|Contains(char c)|Whether c in the encoding
LgR()|How many bits to hold the index|R()|return the base encoding
ToChar(int index)|Transform index to character|ToIndex(char c)|Get the index of character c
# KMP（Knuth-Morris-Pratt）algorithm
There are some constraints defined in the following table in order to simplify description:
Symbol|Meaning|Symbol|Meaning
-|-|-|-
m|strength of pattern|n|strength of text
j|pointer of pattern|i|pointer of text
pat|pattern|txt|text to matched
In hard situation, the time complexity of Brute-force substring search exceeds O(n*m) because of its feature that both of i and j will return their original position when mismatch. However, there are two main characteristics that KMP algorithm do better in the worse condition:
- KMP doesn't make the pointer i return to the original position
- KMP dones't require buffer to hold on text to be search

Rencently, I have been reading a book named *Algortihms(4e)* whose author mixed the definition of DFA with KMP to describe how does KMP work vividly.
Symbol|Meaning
-|-
dfa[][]|matrix indicates the how long should the j return to back
ABABAC|the sample pattern in my passage
For every character c in txt, after comparing pat[j], dfa[c][j] should save the **next** position of pattern which the next position of txt should compare with. It is obvious that the the **next** character of pattern will compare with txt[i+1]. If c equals the current character of pattern, dfa[pat[j]][j] should sace the next position of j——j+1. Otherwise, we know not only substring of txt that can be represented by pat[0...j-1] but the current character of txt. 
## The next position of j（txt[i] not equals pat[j] ）
Let pat[0...j-1] combine with txt[i], and then move the new string from left to right of pattern in order to match the overlapping substring or empty if no-overlapping can be found. We can get information from the picture shown below:
1. If pat[j] equals txt[i], it seen like that the next position, also known as status in terms of DFA, is dfa[pat[j]][j]——j+1.
2. If pat[j] doesn't equal txt[i], j should return to m - dfa[txt[i]][j].
3. i need self-increment.
## DFA simulation
We can simulate the return progression of j using dfa matrix:
![DFA 模拟](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_dfa_sim.png)
## Build DFA matrix
As we described above, there are two situations：
1. txt[i] equals pat[j]
2. txt[i] doesn't equal pat[j]

Let X[j] stand for what the status after correctly input txt[1...j-1] should enter. If we input p[1...j-1] + c, the next status that we should enter is dfa[c][X[j]](When we currently input character c, the overlapping length of pat[1...j-1] + c.). So dfa[c][j] = dfa[c][X[j]]. X[j+1] is next status of inputing p[1...j], it means that input p[j] in the current status——X[j], aka dfa[pat[j]][X[j]]. In conclusion, X[j+1]=dfa[pat[j]][X[j]].

**For the situation 1** Now we should compute what does the txt[i+1] should compare with the next status of pattern. Because the prevoius scanning for overlapping substring is subprocedure, we need not make j return in order to scan overlapping parts. The string of pat[1...j-1] should be scanned. We ignore the first character because of the pattern need to move forward while ignore the last one due to mismatching. 
![构建 DFA 数组](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_pat.jpg)
**For the situation 2** Match the correct character, the pattern need to enter status dfa[pat[j]][j] which is equal to j+1
```csharp
dfa[alphabet.ToIndex(pattern[0]), 0] = 1;//The first status
            for (int X = 0, j = 1; j < M; ++j)
            {
                // j is position of pattern
                for (int c = 0; c < R; ++c)
                {
                    dfa[c, j] = dfa[c, X];//copy subprocedure status
                }
                int rj = alphabet.ToIndex(pattern[j]);//the index of the current character of pattern
                dfa[rj, j] = j + 1;//If match, update the next status
                X = dfa[rj, X]; // Change the restart position
            }
```
But we may need a large dfa matrix to hold on more character due to big range of different encoding such as UNICODE, UTF-8 and etc. So we need use a determinate loop to calculate and update the dfa matrix(by using the recurrence formula).

Every step of building DFA matrix：
![构造DFA](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_construct_dfa.png)
Because dfa matrix is available, we can determine the next status the pattern enter whether  pat[j] and txt[i] match or not.
![根据 DFA 进行匹配](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_dfa_sim.png)
```csharp
/// <summary>
    /// KMP algorithm
    /// </summary>
    class KMP: ISearch
    {
        private string pattern;//pattern
        private int[,] dfa;//matrix 
        private Alphabet alphabet;
        public KMP(string pattern, Alphabet alphabet)
        {
            this.pattern = pattern;
            int R = alphabet.R();
            this.alphabet = alphabet;
            //build matrix
            int M = pattern.Length;
            dfa = new int[R, M];
            // please refer to the code building DFA matrix
        }
        public int Search(string txt)
        {
            int i, j, N = txt.Length,M=pattern.Length;
            for(i=0,j=0;i<N && j < M;++i)
            {
                j = dfa[alphabet.ToIndex(txt[i]), j];//get next status of pattern
            }
            if (j == M)
                return i - M;
            else
                return N;//no matching
        }
        public static void Main()
        {
            Console.WriteLine("模式串(小写英文字母)：");
            string pat, txt;
            pat = Console.ReadLine();
            Console.WriteLine("待匹配串(小写英文字母)：");
            txt = Console.ReadLine();
            KMP kmp = new KMP(pat, Alphabet.LOWERCASE);
            int pos = kmp.Search(txt);
            Console.Write("   Text:{0}\nPattern:", txt);
            if (pos > txt.Length)
                Console.WriteLine("<No pattern found>");
            else
                Console.WriteLine("{0}{1}", new string(' ', pos), pat);
            Console.ReadLine();
            
        }
    }
```
Result is:
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/kmp-scahrp-result.png)

# Bibliography
1. Some pictures and codes are adapted or cited from **Algorithm(4e)**
2. **Algorithm(4e)**
3. [(CSDN)Understand KMP based on DFA](https://blog.csdn.net/congduan/article/details/45459963)
4. [(Douban Book)The subprocedure of dfa](https://book.douban.com/subject/19952400/discussion/59623403/)