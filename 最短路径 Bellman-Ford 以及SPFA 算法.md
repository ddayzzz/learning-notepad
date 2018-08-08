# 最短路径 Bellman-Ford 以及SPFA 算法
- 边的松弛：放松边 v->w 意味着检查从 s 到 w 的最短路径是否先从 s 到 v，然后再由v -> w。如果是，则需要更新数据。
- 如果一个图出现了负的环，那么从 s 到 v，期间如果有点是属于负权环的话，则从 s 到 v 没有最短路径，应该是负无穷。（只要绕着这个环转圈）
### Bellman-Ford 算法
> 对于图 G<V, E> 给定的起点s，从s 无法到达任何一个属于负权环的顶点。将 distTo[s] 初始化为0，其他的 distTo[] 元素初始化为无穷大，任意的顺序放松所有的边，重复 V 轮。算法的复杂度显而易见是 O(VE)

由于没有指定放松的顺序，所以实现很多。使用基于队列的 Bellman-Ford 可以降低复杂度。
### SPFA(基于队列的 Bellman-Ford)
由于普通的 Bellman-Ford 算法会把不需要放松的边（distTo[v] 已经记录的是从 s-> v
的最短的权，所以后续的放松操作没有必要）所以我们只需要放松 distTo[v] 被修改的顶点 v 的关联的边即可。所以我们可以通过类似于 BFS 的探测方法将待放松的边的顶点不重复地加入队列中。
##### 1.放松操作
```csharp
public void Relax(DirectedWeightedGraph g, int v)
        {
            foreach (DirectedEdge edge in g.GetEdge(v))
            {
                int w = edge.End;
                //如果存在放松的条件
                if (distTo[w] > distTo[v] + edge.Weight)
                {
                    distTo[w] = distTo[v] + edge.Weight;
                    edgeTo[w] = edge;
                    //添加为需要继续放松的
                    if(this.onQueue[w] == false)
                    {
                        this.queue.Enqueue(w);
                        this.onQueue[w] = true;
                    }
                }
                if (cost++ % g.V == 0)//根据436 页 定理X.V 个不含有负权环图的节点，放松 V 次可以得到最短路径
                {
                    FindNegtiveCycle();
                    if (HasNegativeCycle())
                        return;
                }
                
            }
        }
```
#### 2.算法的定义
```csharp
class BellmanFordSP:IShortestPath<DirectedEdge>
    {
        private double[] distTo;
        private DirectedEdge[] edgeTo;
        private bool[] onQueue;//节点是否对应于
        private Queue<int> queue;//保存待放松的节点
        private int cost;//放松的次数
        private IEnumerable<DirectedEdge> cycle;//是否有负权？
        public BellmanFordSP(DirectedWeightedGraph g, int s)
        {
            distTo = new double[g.V];
            edgeTo = new DirectedEdge[g.V];
            onQueue = new bool[g.V];
            queue = new Queue<int>();
            for (int i = 0; i < g.V; ++i)
                distTo[i] = double.PositiveInfinity;
            distTo[s] = 0.0;
            queue.Enqueue(s);
            while(queue.Count > 0 && !HasNegativeCycle())
            {
                int v = queue.Dequeue();
                onQueue[v] = false;
                Relax(g, v);
            }
        }
        
        public void Relax(DirectedWeightedGraph g, int v);
        private void FindNegtiveCycle();
        public bool HasNegativeCycle();
        public IEnumerable<DirectedEdge> NegativeCycle();
        public Double DistTo(Int32 v)
        {
            return this.distTo[v];
        }

        public Boolean HasPathTo(Int32 v)
        {
            return this.distTo[v] != Double.PositiveInfinity;
        }

        public IEnumerable<DirectedEdge> PathTo(Int32 v)
        {
            if (!HasPathTo(v))
                return null;
            Stack<DirectedEdge> edges = new Stack<DirectedEdge>();
            //向前退。自己到自己是没有路径的
            for (DirectedEdge edge = edgeTo[v]; edge != null; edge = edgeTo[edge.Src])
            {
                edges.Push(edge);
            }
            return edges;
        }
        public static void Main()
        {
            var fs = System.IO.File.OpenText("tinyEWDnc.txt");
            DirectedWeightedGraph graph = new DirectedWeightedGraph(fs);
            BellmanFordSP sP = new BellmanFordSP(graph, 0);
            if(sP.HasNegativeCycle())
            {
                Console.WriteLine("存在一个负权");
                foreach(var edge in sP.NegativeCycle())
                {
                    Console.WriteLine(edge);
                }
            }
        }
    }
```
#### 3.查找负权重环的边
如果不存在一个可达的负权重的环，那么算法会在 V-1 轮操作后结束（所有最短路径把包含的边数都不会大于 V-1）。所以当且仅当所有边放松 V 轮后且队列非空的时候才存在从起点可达的负权重环。如果有一个负的边，算法会一直将 distTo 减小直到满足了```Relax```中的寻找负权重的环条件。
```csharp
private void FindNegtiveCycle()
        {
            int V = edgeTo.Length;//图的节点数量
            DirectedWeightedGraph spt = new DirectedWeightedGraph(V);
            for(int v=0;v<V;++v)
            {
                if (edgeTo[v] != null)
                    spt.AddEdge(edgeTo[v]);
            }
            DirectedCycle cf = new DirectedCycle(spt);
            //需要转换形式
            Queue<DirectedEdge> edges = new Queue<DirectedEdge>();//没有还
            var list = cf.Cycle().ToList();
            if (list.Count == 0)
                return; 
            for(int i=0;i<list.Count - 1;++i)
            {
                edges.Enqueue(new DirectedEdge(list[i], list[i + 1], spt.GetWeight(list[i], list[i + 1])));
            }
            cycle = edges;
        }
        public bool HasNegativeCycle()
        {
            return cycle != null;
        }
        public IEnumerable<DirectedEdge> NegativeCycle()
        {
            return cycle;
        }
```
一但开始寻找负权重环时，将所有已经存在的边加入新的图中然后 DFS 检测环就可以求一个负权环的边了。
# Dijkstra 拓扑排序优化
没有经过优化的 Dijkstra需要重新搜索出从 s 到 v 的最短的边，然后修改以此为基础的从 v-> w 的最短的权。一下有两种优化：
### 1.使用最小堆
IndexPriorityQueue<Double> 是一个保存 double 的最小堆（可以添加索引）。
```csharp
private DirectedEdge[] edgeTo;//edgeTo[i] 保存的是 到 i 的边
        private Double[] disTo;// disTo[i] 保存的是从 v 到 i 的距离
        private Chapter2.SortDemos.IndexPriorityQueue<Double> pq;//始终有最短的边
        /// <summary>
        /// 构造函数
        /// </summary>
        /// <param name="graph">有向加权非负权图</param>
        /// <param name="s">起点</param>
        public DijkstraSP(DirectedWeightedGraph graph, int s)
        {
            edgeTo = new DirectedEdge[graph.V];
            disTo = new Double[graph.V];
            pq = new Chapter2.SortDemos.IndexPriorityQueue<double>(graph.V);
            for(Int32 v=0;v<graph.V;++v)
            {
                disTo[v] = Double.PositiveInfinity;
                
            }
            disTo[s] = 0.0;//自己到自己为0
            pq.Insert(s, 0.0);
            while (!pq.IsEmpty())
                Relax(graph, pq.DeleteMin());//放松节点，找到到某个点的最短的距离
        }

        public Double DistTo(Int32 v)
        {
            return this.disTo[v];
        }

        public Boolean HasPathTo(Int32 v)
        {
            return this.disTo[v] != Double.PositiveInfinity;
        }

        public IEnumerable<DirectedEdge> PathTo(Int32 v)
        {
            if (!HasPathTo(v))
                return null;
            Stack<DirectedEdge> edges = new Stack<DirectedEdge>();
            //向前退。自己到自己是没有路径的
            for (DirectedEdge edge = edgeTo[v]; edge != null; edge = edgeTo[edge.Src])
            {
                edges.Push(edge);
            }
            return edges;
        }

        private void Relax(DirectedWeightedGraph g, int v)
        {
            foreach(DirectedEdge edge in g.GetEdge(v))
            {
                int w = edge.End;
                //如果存在放松的条件
                if (disTo[w] > disTo[v] + edge.Weight)
                {
                    //存在，需要更新
                    disTo[w] = disTo[v] + edge.Weight;
                    edgeTo[w] = edge;
                    //重新累计从 s 到某个点的最短距离。这个距离只有可能减小
                    if (pq.Contains(w))
                        pq.Change(w, disTo[w]);
                    else
                        pq.Insert(w, disTo[w]);
                }
            }
        }
        public static void Main()
        {
            System.IO.TextReader s = System.IO.File.OpenText("tinyEWDAG.txt");
            DirectedWeightedGraph graph = new DirectedWeightedGraph(s);
            Console.Write("请输入起点：");
            int st = int.Parse(Console.ReadLine());
            DijkstraSP dijkstraSP = new DijkstraSP(graph, st);
            for(int i=0;i<graph.V;++i)
            {
                if (i == st)
                {
                    Console.WriteLine("{0}->{0} : 0.0", st);
                    continue;
                }
                if(!dijkstraSP.HasPathTo(i))
                {
                    Console.WriteLine("{0}->{1} : No paths", st, i);
                    continue;
                }
                Double len = 0.0;
                foreach(var edge in dijkstraSP.PathTo(i))
                {
                    Console.Write(edge);
                    Console.Write(",");
                    len += edge.Weight;
                }
                Console.WriteLine(" : {0}", len);
            }
        }
    }
```
### 2.使用拓扑排序
对于解决**无环图** G<V,E> 的情况，拓扑排序优化的 Dijkstra 算法可以在 E+V 的时间内解决单点最短路径问题。任意一个点 v，他的放松操作后不会再放松其他的已经被放松的顶点关联的边了（边被放松的时候，起点是必须能够被访问的，但是已经访问的顶点会被标记！）
```csharp
class AcyclicSP : IShortestPath<DirectedEdge>
    {
        private DirectedEdge[] edgeTo;//edgeTo[i] 保存的是 到 i 的边
        private Double[] distTo;// disTo[i] 保存的是从 v 到 i 的距离
        /// <summary>
        /// 构造函数
        /// </summary>
        /// <param name="g">有向加权非负权图</param>
        /// <param name="s">起点</param>
        public AcyclicSP(DirectedWeightedGraph g, int s)
        {
            edgeTo = new DirectedEdge[g.V];
            distTo = new Double[g.V];
            for (Int32 v = 0; v < g.V; ++v)
            {
                distTo[v] = Double.PositiveInfinity;
            }
            Topological top = new Topological(g);

        }

        public Double DistTo(Int32 v);
        public Boolean HasPathTo(Int32 v);
        public IEnumerable<DirectedEdge> PathTo(Int32 v);

        private void Relax(DirectedWeightedGraph g, int v)
        {
            foreach (DirectedEdge edge in g.GetEdge(v))
            {
                int w = edge.End;
                //如果存在放松的条件
                if (distTo[w] > distTo[v] + edge.Weight)
                {
                    distTo[w] = distTo[v] + edge.Weight;
                    edgeTo[w] = edge;
                }
            }
        }
    }
```
# 复杂度
算法|要求|路径长度比较次数（增长的数量级）|所需的空间
-|-|-|-
Dijkstra（即使版本，堆）|所有的权为正|ElogV（最好和最坏）|V
Dijkstra|无环|E+V（最好和最坏）|V
SPFA|不带有负权环的图|E+V（一般情况），VE（最坏，稀疏图）| V
# 参考
- [（CSDN）浅谈路径规划算法之Bellman-Ford算法](http://blog.csdn.net/AK_Lady/article/details/70147204)
- [（算法4）BellmanFordSP.java](https://algs4.cs.princeton.edu/44sp/BellmanFordSP.java.html)
- Robert Sedgewick等，算法（第四版）[M]. 北京：人民邮电出版社，2012：433-442