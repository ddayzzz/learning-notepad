# B树
B树（有时候也叫 B- 树）与 AVL 树一样是平衡树。比如各种文件系统，可能需要把一些已经排过序的数据存放在离散的节点之中。如果使用其他的树，例如二叉搜索树，如果插入的是顺序的数据，那么二叉搜索树就会退化为线性的查找（深度太大）。每次访问某个非叶子节点，就需要比较它的键值（这个时候就需要访问磁盘，进一步增加了查找的时间）。所以我们需要减小深度，但是查找的次数是不变的，这样的话我们就必须要减小“向下”访问子树的次数，在当前的节点中比较尽可能多的东西。B树就是一个多路搜索树。
# B树的几个定义
我们按照情况定义一个变量 M（一般是偶数），这个指出一个节点中保存多少个键：
- 对于普通的节点，必须保证键的范围为 [M/2, M-1]。M/2 的下界保证可以提供多个分支保证查找的路径最短。
- 根节点可以保存少于 M/2 的键。
- 一个节点的子树总是比键的数量多 1，因为键之间保存了大小的信息。
- 最终所有的叶子节点保存的是来自外部的引用，比如文件系统的某个 Node
![B树的定义](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-btree/btree-def.png)
- 图中的哨兵键是 B树中最小的键值
- 每个子树的键都是父节点中键的副本
- 根节点在初始化的时候只有哨兵键
# 向上分解
由于 B树 是一个平衡树，类似于红黑树的在不平衡的时候需要向上分解：
例如插入 A：
![插入A](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-btree/btree-insertA.png)
由于插入的新节点 A 小于 B，所以自然会向下定位到原 B 的外部叶子节点处。将 A 插入到指定的外部节点就不满足上界的定义了，所以向上将 ABCEF 分解为两个新的节点。同时复制键。向上分解后，根节点保存了分解出的第二个节点的最小的键（因为有序）。根节点也需要按照同样的步骤分解。
# 查找
查找和二叉搜索树类似，因为所有的键的有序的只需要比较与键的关系直接向下直到找到或者没有叶子节点的键关联的外部引用。
# B树 的定义
## 定义描述节点的类
- Entry：这是表示 B树 中键-子树的链接以及下一个 Entry 的指针域。
- Node：Node 中需要保存 M 个 Entry，以及键值。
![B树 Entry 和 Node 类](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-btree/btree-class.png)
## 查找某个键，返回值
### 私有方法
```c#
private TValue Search(Node x, TKey key, int ht)
        {
            Entry[] children = x.children;
            if(ht == 0)
            {
                for (int j = 0; j < x.m; ++j)
                    if (key.CompareTo(children[j].key) == 0)
                        return children[j].value;
            }
            else
            {
                //内部节点
                for (int j = 0; j < x.m; ++j)
                    if (j + 1 == x.m || key.CompareTo(children[j + 1].key) < 0)
                        return Search(children[j].next, key, ht - 1);// ht - 1 向下查找。注意 如果找到了小于的键值，注意这个是后一个，可能还有相等的
            }
            return null;//没有找到
        }
```
注意 ht 参数指定了当前插入的是内部节点还是外部节点。ht 是当前访问节点的高度。因为我们不能确定一棵树的的动态的高度是多少从而判断是否是外部节点。如果 ht==0，那么表示已经访问到了存放外部引用的节点，所以只需要判断是否存在与 key 相等的某个外部引用的键。
## 分解某个饱和的键
```c#
private Node Split(Node h)
        {
            Node t = new Node(M / 2);
            h.m = M / 2;
            for (int j = 0; j < M / 2; ++j)
                t.children[j] = h.children[M / 2 + j];//拷贝后面的部分
            return t;//返回后面的 M/2 部分
        }
```
按照分解过程的定义，我们最终向上添加的键一定是新生成的第二个节点的最小的键，而且第一个节点还是可以继续保存，只不过缩小到了一半。
## 插入某个键
### 私有的方法
```c#
private Node Insert(Node h, TKey key, TValue value, int ht)
        {
            int j;
            Entry t = new Entry(key, value, null);
            //外部节点。也就是叶子
            if (ht == 0)
            {
                for (j = 0; j < h.m; ++j)
                    if (key.CompareTo(h.children[j].key) < 0)
                        break;//j 更新为比key小的位置
            }
            else
            {
                // 内部节点
                for(j=0;j<h.m;++j)
                {
                    if((j+1 == h.m)/*是否是最后一个元素到了*/ || key.CompareTo(h.children[j+1].key) < 0)// key < j+1位置的键值
                    {
                        Node u = Insert(h.children[j++].next, key, value, ht - 1); // 在他的上界范围内插入，注意还要向下插入到关键字的指针
                        if (u == null)
                            return null;//不需要分解
                        t.key = u.children[0].key; // 经过了分解。此时的 u 是后面的一部分。按照 P569 所示。从后半部分选择最小的作为分解出来的一个节点的最小。
                        t.next = u;//将分出的节点设置新的连接
                        break;
                    }
                }
            } // state1
            // 这个是数组的插入操作，需要保持顺序所以向后移动一位
            for (int i = h.m; i > j; --i)
                h.children[i] = h.children[i - 1];
            h.children[j] = t;
            //state2
            h.m++;
            if (h.m < M)
                return null;
            else
                return Split(h);//不满足条件了需要向上分解
        }
```
这段代码稍微复杂：

**state1 上面的情况**：j 是用来确定插入的位置。对于内部节点，如果找到了第一个大于待插入键(也就是 j+1）或者已经到了最后一个的位置（也就是待插入的键比当前节点的所有的键还大），需要插入到当前的位置，也就是 j。如果插入没有返回新分解的产生的两个节点的第二个节点，表示成功插入到子树而且不需要分解。那下面的代码没有什么意义，因为这个待插入的键不需要在当前的节点留下键副本（因为它仅仅是介于两个节点之间的键）

**state1 到 state2 的情况**：这一部分都懂，和插入排序类似。

**state2 下面的情况**：更新大小，看看是否需要分解。也即是已经存在的节点是否介于 [0, M-1]，否则分解。

[完整的代码](https://github.com/ddayzzz/Algorithms-In-CSharp/blob/master/Algorithms%20In%20CSharp/Context/BTree/BTree.cs)

示例，M=6，可以看到树的高度为 4，一共插入了 459 个节点。如果按照普通二叉树的定义，这么多的节点高度至少是 floor(log2(459)) + 1 = 9。可以看出对于每个叶子节点都是相同的缩进，而且是有序的。这里的外部引用就是域名的 ip 地址。
![M=6 的B树](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/algs-btree/btree-demo.png)。
# 参考&引用
1. [C# 代码改编自《算法（第四版）》](https://algs4.cs.princeton.edu/code/edu/princeton/cs/algs4/BTree.java)
2. [（CSDN）B树](https://blog.csdn.net/v_JULY_v/article/details/6530142)