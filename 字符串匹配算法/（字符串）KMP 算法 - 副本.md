# Alphabet 类
这个类是处理各种字符串所需要用的字符串组成要素（字符集合）的抽象描述。具体的实现参见 [Github](https://github.com/ddayzzz/Algorithms-In-CSharp/blob/master/Algorithms%20In%20CSharp/StringDemo/Alphabet.cs)。部分接口：
方法|说明|方法|说明
-|-|-|-
Alphabet()|构造，默认的字符编码范围[0, 256)|Alphabet(int R)|构造，默认默认编码范围[0,R)
Alphabet(string s)|根据s中无重复的字符构造，范围是[0,S)，S 是无重复的字符个数|Contains(char c)|字母表是否存在 c 字符
LgR()|表示一个索引需要的比特数|R()|字母表的基数
ToChar(int index)|获取索引，转换为字符|ToIndex(char c)|将字符转换为索引
# 字符串 KMP（Knuth-Morris-Pratt）算法
几个约定：
符号|意义|符号|意义
-|-|-|-
m|模式串长度|n|文本长度
j|模式串的指针|i|文本串指针
pat|模式串|txt|文本串
传统的暴力模式的子字符串匹配在最坏的情况下的复杂度达到 O(n*m)。KMP 算法可以通过不回退文本指针 i 并且 j 回退到恰当的位置而将复杂度降低到 O(n+m)。以前对 KMP 算法不甚了解，今天重新通过《算法（第四版）》中介绍的 DFA (确定性有限状态自动机) 来解释 KMP 算法的精妙之处。

约定|意义
-|-
dfa[][]|二维数组，表明当模式匹配失败的时候，指针 j 应该回退多远
ABABAC|示例用的模式串
对于 txt 中的字符 c，比较了 pat[j] 之后，dfa[c][j] 应该保存的是和下一个文本字符比较的**模式**串的位置，这个位置要和 txt[i+1] 开始比较，所以在模式串和文本串的位置上的字符匹配之后，dfa[pat[j]][j] 应该返回的是 j+1。如果不匹配，不仅可以知道 txt[i] 的字符也可以知道文本中的前 j-1 个字符（从开始进行匹配操作的位置开始的位置）即 pat[0...j-1]。所以我们可以通过不匹配的字符（字符是有限的）。
## j 的回退（此时 txt[i] 与 pat[j] 不相等）
将 pat[0...j-1] 与 txt[i] 字符组合。然后将这个字符串从左向右移动与**模式串**进行重叠部分的匹配（或者没有匹配）直到停下来。从图中可以看出：
1. 如果 pat[j] 与 txt[i] 相等，表明 pat 应该进入的模式是 dfa[pat[j]][j] 也就是 j+1。
2. 如果 pat[j] 与 txt[i] 不相等，j 应该回退到 m - dfa[txt[i]][j] 的位置。
3. i 只需要加1
## DFA 模拟
有了 j 的回退数组就可以进行 DFA 模拟：
![DFA 模拟](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_dfa_sim.png)
## 构建 DFA（计算 dfa 数组）
这里有两种情况：
1. 当 txt[i] 与 pat[j] 不匹配时
2. 当 txt[i] 与 pat[j] 匹配时

设 X[j] 表示正确输入 p[1...j-1] 字符串后模式串进入的状态。输入 p[1...j-1] + c 进入的后的状态就是 dfa[c][X[j]] (表示当输入的字符是 c 的时候，模式串与pat[0...j-1] + c 重叠部分的长度。在 X[j]的状态下输入 c)。所以 dfa[c][j] = dfa[c][X[j]]。X[j+1] 为输入 p[1...j] 进入的状态，也就是在 X[j] 状态下输入 p[j] 进入的状态，也就是 dfa[pat[j]][X[j]]， 可得递推公式 X[j+1]=dfa[pat[j]][X[j]]。

**对于情况1：** 这个时候需要计算的是下一次 txt[i+1] 需要和模式串中的从哪一个开始的字符串需要被扫描。注意我们不需要将 j 回退以扫描重叠的部分，因为之前进行的扫描也是一个子过程。pat[1...j-1] 的字符串需要被扫描。忽略第一个字符是因为模式串需要右移一位；忽略最后一位是因为不匹配。
![构建 DFA 数组](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_pat.jpg)
**对于情况2：** 直接进入状态 j+1，即 dfa[pat[j]][j] = j+1。

但是对于具有很长的编码范围的编码（UNICODE 等）分配一个 X 数组需要开辟很多的空间。所以使用 X 变量然后在循环内（根据递推公式）更新 dfa 数组。
```csharp
dfa[alphabet.ToIndex(pattern[0]), 0] = 1;//当前输入是模式串的时候，模式指针向前移动
            for (int X = 0, j = 1; j < M; ++j)
            {
                // j 表示的是模式字符的位置
                for (int c = 0; c < R; ++c)
                {
                    dfa[c, j] = dfa[c, X];//拷贝X列到j的位置
                }
                int rj = alphabet.ToIndex(pattern[j]);//真实的对于R范围内的缩索引
                dfa[rj, j] = j + 1;//具有相同的输入的位置
                X = dfa[rj, X];
            }
```
构造DFA 模拟的过程图解：
![构造DFA](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_construct_dfa.png)
有了 dfa 数组就可以处理 pat[j] 和 txt[i] 匹配/不匹配的时候 j 移动/回退的距离。
![根据 DFA 进行匹配](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/sample_dfa_sim.png)
```csharp
/// <summary>
    /// KMP 算法：使用了经过 DFA 处理的状态数组。参考博客 https://blog.csdn.net/congduan/article/details/45459963
    /// </summary>
    class KMP: ISearch
    {
        private string pattern;//模式串
        private int[,] dfa;//状态机
        private Alphabet alphabet;
        public KMP(string pattern, Alphabet alphabet)
        {
            this.pattern = pattern;
            int R = alphabet.R();
            this.alphabet = alphabet;
            //构建DFA自动机
            int M = pattern.Length;
            dfa = new int[R, M];
            // 参考 构造 DFA 代码
        }
        public int Search(string txt)
        {
            int i, j, N = txt.Length,M=pattern.Length;
            for(i=0,j=0;i<N && j < M;++i)
            {
                j = dfa[alphabet.ToIndex(txt[i]), j];//输入进行向上转移
            }
            if (j == M)
                return i - M;
            else
                return N;//没有匹配
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
# 字符串 BM（Boyer-Moore）算法
BM 算法采用的另外的一中思路：从右向左移动 j 指针，如果出现了匹配失败，就会产生三种情况。这个算法同样不会回退 i 指针。设失配的字符为 c = txt[i + j]：
1. c 没有出现在模式串中
2. c 出现在模式串中

**对于情况1：** 这个时候将整个模式串的 j 回退到 m-1，将 i 更新为 i + m

**对于情况2：** 需要计算的是是否存在一个位置，这个位置是 txt[i] 出现在最右边的位置。如果存在，i 更新为 i + j（更新后）的位置；不存在就是**情况 1**
![BM 算法启发式搜索](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/bm-process-unmatching-char.png)
为了简化运算，使用 right[i] 数组表示当 c 在字母表中索引为 i 的时候，c 在**模式串**中最右侧的地址。下面继续探讨这两个情况：

**对于情况1：** 这个时候 right[i] 应该赋值为 -1，因为没有位置可以移动的位置。所以将 i 增大 j - right[txt[i+j]]（注意 i 总是模式匹配 j 与 之前的 i + j 对齐的位置）。此时 i += j + 1。j 更新为 m - 1。

**对于情况2：** 根据 i += j - right[txt[i+j]]。但是这里会出现一个特殊的情况即：
![BM 特殊的情况](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/bm-special-pat.jpg) 。可以发现相减后 i 指针会往后退或者原地不动。为了避免这种情况，需要进行判断！
```csharp
/// <summary>
    /// Boyer-Moore 算法
    /// </summary>
    class BM: ISearch
    {
        private int[] right;//文本串当发生不匹配的时候，向右移动的距离
        private string pattern;
        private Alphabet alphabet;//字母表
        public BM(string pat, Alphabet alphabet)
        {
            this.pattern = pat;
            this.alphabet = alphabet;
            int M = pat.Length, R = alphabet.R();
            right = new int[R];
            for (int c = 0; c < R; ++c)
                right[c] = -1;
            for (int j = 0; j < M; ++j)
                right[alphabet.ToIndex(pat[j])] = j;//包含在模式中的字符表示为其中出现在最右侧的位置
        }
        public int Search(string txt)
        {
            int N = txt.Length;
            int M = pattern.Length;
            int skip;
            for (int i = 0; i <= N - M; i = i + skip)
            {
                skip = 0;
                for (int j = M - 1; j >= 0; --j)
                {
                    if (txt[i + j] != pattern[j])
                    {
                        //发生了不匹配
                        skip = j - right[alphabet.ToIndex(txt[i + j])];
                        if (skip < 1)
                            skip = 1;
                        //有几种情况：txt 未匹配的字符不在模式串中，那么i递增j+1
                        // txt 未匹配的字符在模式字符串中：i = i + j - right[字符]。注意这个字符可能出现在>=j的位置
                        //   当 right[字符] > j 的时候 : j - right[字符] < 0 , 就会将 i 左移，为了避免这种情况，需要 skip=1, 保证txt 向右了
                        //                  < j 的时候，j - right[字符] 就是 i 需要向右移动的距离
                        //                  = 不可能
                        break;
                    }
                }
                if (skip == 0)
                    return i;
            }
            return N;//不匹配
        }
        public static void Main()
        {
            Console.WriteLine("MP:");
            Console.Write("模式串(小写英文字母)：");
            string pat, txt;
            pat = Console.ReadLine();
            Console.Write("待匹配串(小写英文字母)：");
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
# NFA 和 正则表达式
由于正则表达式允许或的存在所以需要使用一个更加抽象的自动机。NFA（非确定性有限状态自动机）在面对当前模式匹配的多种可能的时候，自动机能够“猜出”正确的转换。与 KMP 算法运行模拟过程相似，需要经过几个步骤：
1. 构造和给定正则表达式对应的 DFA
2. 模拟 NFA 在文本串的运行轨迹。

Kleene 定理证明了对于任意的正则表达式都存在一个与之对应的非确定性有限状态自动机。设要处理的正则表达式 ((A*B)|AC)D) 定义的 NFA 有几个特性：
- 长度为 M 的正则表达式的每一个字符有且只对应于一个状态。NFA 的初始化状态是 0 到 终止状态 M。
- 字母表的字符所对应的状态都有一条边指向模式中下一个字符的所对应的状态（**黑色边**）。
- 元字符“(”，“)”，“|” 和 “*” 所对应的状态至少含有一条指出的**红色边**。

所构造的 NFA：
![模式 ((A*B)|AC)D) 构建的 NFA 自动机](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/nfa-regexp-sample.png)
NFA 与 DFA 的不同之处：
- 每个字符是一个状态而不是边
- NFA 必须要读取文本串的所有字符才能识别，而 DFA 不需要（DFA 只需要找到一个匹配即可）

NFA 在进行状态的转换的时候有两种情况：
- 匹配转换：如果文本串与模式串匹配，则通过黑色边转换到下一个状态
- E 转换：通过红色边转换而不扫描文本中的任何字符。
![找到与 ((A*B)|AC)D) 匹配的模式](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/nfa-matching-pattern.png)

所以当且仅当一个 NFA 从状态 0 开始从头读取了一段文本中的所有字符，进行一系列的转换最终转换为状态 M，则 NFA 识别了这个文本串。否则（使 NFA 停滞在某个状态），则称无法识别。
## 模拟 NFA 运行
使用有向图构建 NFA。已知这个 NFA 中存在**有向边**：0->1, 1->2, 1->6, 2->3, 3->2, 3->4, 5->8, 8->9, 10->11。我们知道从 0 状态开始有几个可以达到的状态 1，2，3，4 和 6。
![对于输入 AABD 对于 ((A*B|AC)D)](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/nfa-matching-sim.png)
模拟的算法是如下进行的：如果文本串的字符能够是 NFA 通过以当前集合的为起点经过某一条有向边到达下一个状态。那么更新当前的集合为 下一个状态能达到到的状态的集合。例如 NFA 的初始状态的集合是 {0,1,2,3,4,6} 那么输入字符 A，通过 NFA 的匹配转换到状态 2，从状态 2 能够到达的状态机集合是 {3,7}，他接下来可能进行从 3 到 2 或者 3 到 4 的 E转换，因此可能与第二个字符匹配的状态机集合为 {2,3,4,7}.重复这个过程直到接受或者无法转换到状态 M。
**注意：**
- ‘*’的处理：当从前一个状态转换过来，下一个 E转换 指向了转换前的状态（例如2->3，3->2）。这样在下一次的确定可能的状态的时候，前一个状态就可以包含在其中。
- 模式串中的**元字符**部分会在遍历所有的状态是时自动排除
使用有向图的深度优先遍历加上循环就可以每步求出状态机集合：
```csharp
/// <summary>
        /// 判断NFA能否识别的文本txt
        /// </summary>
        /// <param name="txt">文本</param>
        /// <returns></returns>
        public bool Recognizes(string txt)
        {
            Bag<int> pc = new Bag<int>();//这个保存某个状态通过E-转移或者某种输入的移动。初始化时，使用状态0的可达的状态
            DepthFirstSearch dfs = new DepthFirstSearch(G, 0);
            for (int v = 0; v < G.V; ++v)
                if (dfs.HasPathTo(v))
                    pc.Add(v);//如果从0状态到某种状态是可行的
            for(int i=0;i<txt.Length;++i)
            {
                Bag<int> match = new Bag<int>();
                foreach(var v in pc)
                {
                    if(v < M)//是一个状态
                    {
                        if (re[v] == txt[i] || re[v] == '.')
                            match.Add(v + 1);//如果是任何一种类型的转换。1
                    }
                }
                pc = new Bag<int>();
                dfs = new DepthFirstSearch(G, match);//将所有可以到的点加入（当前匹配或者'.'）
                for (int v = 0; v < G.V; ++v)
                {
                    if (dfs.HasPathTo(v))
                        pc.Add(v);
                }//新的可达
            }
            foreach (var v in pc)
                if (v == M)//虚拟的点，表示结束 1 代码添加后面的状态
                    return true;
            return false;
           
        }
```
## 构造正则表达式对应的 NFA
NFA 的构造类似于使用 Dijkstra 双栈法进行表达式求值的过程。不过，正则表达式有些不同：
- 正则表达的连接操作没有运算符
- 正则表达式的闭包操作(*)是一个一元运算符
- 正则表达式只有一个二元运算符：或(|)

构造的方法：
- 连接操作就是字符与字符之间的连接（匹配转换）
- 闭包：如果 * 出现在字符后面那么添加两个双向的连接；如果出现在右括号后面（就是表示括号中的模式能出现）添加 * 与左括号（栈顶元素）的双向连接。
- 或：例如 (A|B)，需要将给 <|, )> 和 <(, | 的后面一个字符> 添加 E转换。
- 至少出现了一次 +，如果 + 出现在字符后面那么添加一个**单向**连接从 + 指向前一个字符；如果出现在右括号后面（就是表示括号中的模式至少出现一次）添加 + 到 左括号（栈顶元素）的单向连接。
```csharp
/// <summary>
        /// 匹配至少出现一次的运算符 + 
        /// 来自于：https://www.cnblogs.com/catch/p/3722082.html
        /// </summary>
        /// <param name="regexp">正则表达式</param>
        private void doDFA_appearAtLeastOnce(string regexp)
        {
            Stack<int> ops = new Stack<int>();
            re = regexp.ToArray();
            M = re.Length;
            G = new DirectedWeightedGraph(M + 1);
            for (int i = 0; i < M; ++i)
            {
                int lp = i;
                if (re[i] == '(' || re[i] == '|')
                {
                    //左括号或者或运算符
                    ops.Push(i);
                }
                else if (re[i] == ')')
                {
                    int or = ops.Pop();
                    if (re[or] == '|')
                    {
                        //如果右括号，而且之前是或运算符的右边部分
                        lp = ops.Pop();//需要建立某个双向的连接，
                        G.AddEdge(lp, or + 1);//从左括号到或运算符后（不满足的一个）
                        G.AddEdge(or, i);//从左括号到右括号
                    }
                    else
                        lp = or;//如果仅仅是一组字符串而已用()括住的话。lp 设置为左括号的位置。因为括号的优先级是很高的。
                    //所以在栈上括号在 * 顶部
                }
                if (i < M - 1 && re[i + 1] == '*')//查看下一个字符
                {
                    G.AddEdge(lp, i + 1);//这个字符是可以>=0的，双向状态转换
                    G.AddEdge(i + 1, lp);
                } else if (i < M - 1 && re[i + 1] == '+')//查看下一个字符是不是+
                {
                    //G.AddEdge(lp, i + 1);//这个字符是可以>=0的，单向状态转换
                    G.AddEdge(i + 1, lp);
                }

                if (re[i] == '(' || re[i] == '*' || re[i] == ')' || re[i] == '+')
                    G.AddEdge(i, i + 1);//后面的状态
            }
        }
```
几种情况：
![几种情况以及构造 (.*AB((C|D*E)F)*G) 的 NFA](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/nfa-construct-situations.PNG)
构造 ((A*B|AC)D) 的 NFA 时使用的双栈法：
![构造 ((A*B|AC)D) 的 NFA](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-substring-matching/nfa-construct.PNG)
# 参考&引用
1. 图片来自或改编自 《算法（第四版）》
2. Robert Sedgewick等，算法（第四版）[M]. 北京：人民邮电出版社，2012：495-528
3. [（CSDN）从DFA角度理解KMP算法](https://blog.csdn.net/congduan/article/details/45459963)
4. [（豆瓣图书）dfa 数组的递推公式](https://book.douban.com/subject/19952400/discussion/59623403/)
5. CSharp 代码的基本实现参考 《算法（第四版）》
6. [（Cnblogs）NFA 与 正则表达式](https://www.cnblogs.com/catch/p/3722082.html)