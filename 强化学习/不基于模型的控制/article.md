# 不基于模型的控制
主要关于不基于模型的条件下如何通过个体的学习优化价值函数

- **行为策略**：指导个体产生与环境交互的行为的策略
- **目标策略**：评价状态或者行为价值的策略或者待优化的策略
如果个体在学习的过程中优化的策略与行为策略是同一个策略，则称为**现时策略学习(on-policy learning)**，如果不是同一个策略，则称为**借鉴策略学习(off-policy learning)**。
## $\epsilon$-贪婪策略
初始的时候，选择的是均一的随机策略，而经过一次迭代之后，我们选择了贪婪策略，即每一次只选择具有最大价值的状态行为，在随后的每一次迭代的时候都使用这个贪婪策略。但在如TD、MC等不考虑使用完整状态序列的算法中这会导致：

- 一些从未被经历但是价值可能很高的状态无法被考虑。
- 由于经历的次数不多，对状态的价值评估并不一定准确。
所以，假设一个很小是数$\epsilon$，使用$1-\epsilon$的概率贪婪地选择最大价值的行为，而$\epsilon$的概率选择**所有的**m个行为：
$$
\pi(a|s) = 
\left\{ 
\begin{array}{ll} 
\epsilon/m+1-\epsilon & \textrm{如果 }a^*=\arg\max_{a\in A}Q(s,a)\\ 
\epsilon/m & \textrm{其他情况}
\end{array} 
\right.
$$
## 现时蒙特卡罗控制
蒙特卡罗控制一个大概的过程：用Ｑ函数进行策略评估，使用$\epsilon$-贪婪探索来改善策略。该方法最终可以收敛至最优策略。

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/mc_policy_iteration.png)
图中每一个向上或向下的箭头都对应着多个Episode。也就是说我们一般在经历了多个Episode之后才进行依次Ｑ函数更新或策略改善。实际上我们也可以在每经历一个Episode之后就更新Ｑ函数或改善策略。但不管使用那种方式，在$\epsilon$-贪婪探索算下我们始终只能得到基于某一策略下的近似Ｑ函数，且该算法没没有一个终止条件，因为它一直在进行探索。因此我们必须关注以下两个方面：

- 一方面我们不想丢掉任何更好信息和状态。
- 另一方面随着我们策略的改善我们最终希望能终止于某一个最优策略，因为事实上最优策略不应该包括一些随机行为选择。

为此引入了另一个理论概念：GLIE(Greedy in the limit with Infinite Exploration)：
### GLIE
- 所有的状态行为都会被无限次探索：
$$\lim_{k\to\infty}N_k(s,a)=\infty$$
- 采样趋向于无穷多，策略收敛至一个贪婪策略：
$$\lim_{k\to\infty}\pi_k(a|s)=1(a=\arg\max_{a^{'}\in A}Q_k(s,a^{'}))$$
存在如下的定理：GLIE 蒙特卡罗控制能收敛至最优的状态行为价值函数：
$$Q(s,a)\to q^*(s,a)$$
### 基于GLIE的蒙特卡罗控制流程
如果使用$\epsilon$-贪婪策略，能令$\epsilon$随采样的次数增加而趋向于0，就符合GLIE。
完整的流程：

1. 基于给定的策略$\pi$，采样第k个完整的序列：$\{S_1,A_1,R_2,\cdots,S_T\}$
2. 对于该状态序列李出现的每一个状态行为对$(S_t,A_t)$，更新其计数N和行为价值函数Q：
$$N(S_t,A_t)\gets N(S_t,A_t)\\
Q(S_t,A_t)\gets Q(S_t,A_t)+\frac{1}{N(S_t,A_t)}(G_t-Q(S_t,A_t))
$$

3. 基于新的行为价值函数Q以如下的方式改善策略：
$$
\epsilon\gets\frac{1}{k}\\
\pi\gets\epsilon-\textrm{greedy}(Q)
$$
## 现时策略时间差分控制
### Sarsa 算法
针对一个状态S，个体通过一个策略选择一个行为A，进而产生一个状态行为对(S,A)。环境报告回报R以及后续进入的状态$S^{'}$；个体在状态$S^{'}$是遵循当前的行为产生一个新的行为$A^{'}$但并不执行，而是通过行为价值函数得到后一个状态行为对$(S^{'},A^{'})$的价值，利用这个新的价值和即时奖励R更新前一个状态行为对(S,A)的价值。

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/sarsa_example.png)
在每一时间件步（单个Episode中个体在每个状态$S_t$）中，在状态S下采取行为A到状态$S^{'}$都要更新状态行为对(S,A)的价值Q(S,A)。这个过程同样使用$\epsilon$-贪婪策略：
$$Q(S,A)\gets Q(S,A)+\alpha(R+\gamma Q(S^{'},A^{'})-Q(S,A))$$
Sarsa 的算法流程：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/sarsa_on_policy_algorithm.png)
在更新行为价值时，$\alpha$是学习速率，$\gamma$是衰减因子。当行为策略满足GLIE特性时$\alpha$满足：
$$\sum^\infty_{t=1}\alpha_t=\infty,且\sum_{t=1}^\infty\alpha^2<\infty$$
时，Sarsa 算法将收敛至最优策略和最优价值函数。
假设一个网格，横坐标表示当前的风向的强度，所有的方式从下往上吹的。状态空间S为70，空间A具有4个动作。我们不需要掌握环境动力学的基本特征。个体从S出发需要到G的位置，需要找到一个最优的策略：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/gridworld_example_sarsa.png)
个体每移动一次的回报时-1，当抵达G的位置的时候，回报为0并永久停留在终点G。最终个体找到最优的策略以及选择的行为轨迹：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/gridworld_example_sarsa_optimalpath_learning_curve.png)
### $\textrm{Sarsa}(\lambda)$算法
n-步Sarsa 定义n-步Q收获：
$$q_t^{(n)}=R_{t+1}+\gamma R_{t+2}+\cdots+\gamma^{n-1}R_{t+n}+\gamma^nQ(S_{t+n},A_{t+n})$$
n-步Sarsa用n-步Q收获来表示：
$$Q(S_t,A_t)\gets Q(S_t,A_t)+\alpha(q_t^{(n)}-Q(S_t,A_t))$$
类似于$\textrm{TD}(\lambda)$，可以给Q-收获分配权重：
$$q_t^\lambda=(1-\lambda)\sum_{n=1}^\infty\lambda^{n-1}q_t^{(n)}$$
#### 前向认识$\textrm{Sarsa}(\lambda)$
用某一状态的$q_t^\lambda$收获来更新状态行为对的Q值：
$$Q(S_t,A_t)\gets Q(S_t,A_t)+\alpha(q_t^{(\lambda)}-Q(S_t,A_t))$$
可见要更新Q价值需要遍历完整的状态序列。
#### 反向认识$\textrm{Sarsa}(\lambda)$
同之前的$\textrm{TD}(\lambda)$，引入针对状态行为的效用迹E：
$$
E_0(s,a)=0\\
E_t(s,a)=\lambda\gamma E_{t-1}(s,a)+1(S_t=s,A_t=a)\quad\gamma,\lambda\in[0,1]
$$
TD误差$\delta_t$。引入效用迹之后$\textrm{Sarsa}(\lambda)$算法中对Q值的更新：
$$
\delta_t=R_{t+1}+\gamma Q(S_{t+1},A_{t+1})-Q(S_t,A_t)\\
Q(s,a)\gets Q(s,a)+\alpha\delta_t E_t(s,a)
$$
$\textrm{Sarsa}(\lambda)$算法的具体步骤：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/sarsa_lambda_on_policy_algorithm.png)
在算法中：

- $E(s,a)$在每次Epsisode结束之后重置为0，因为针对的是一个完整的状态学列
- 更新Q和E时针对的不是某个状态序列里的Q或E，而是针对个体掌握的整个状态空间和行为空间产生的Q和E。

Sarsa和$\textrm{Sarsa}(\lambda)$算法的区别：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/differences_between_sarsa_and_srasa_lambda.png)
给出约定：

- 认定每一步的即时奖励为0，终点处的即时奖励为1
- 除了终点以外的任何状态行为对的Q值在初始时任意，但我们假设均为0
- 该路线是个体第一次找到终点的路线

**Sarsa算法**：根据定义，在状态S根据当前的策略产生移步行为转移到状态$S^{'}$，同时奖励为0。由于是现时学习，根据当前的策略选择不执行的基于当前新位置的新行为，在表中查找Q值，由于是0，根据更新公式，它把刚才离开的位置以及对应的状态行为对的价值Q更新为0。直到到达终止位置$S_G$，获得即时奖励为1。根据更新公式，达到$S_G$之前的位置$S_H$的状态行为价值$Q(S_H,A_{\textrm{向上}})$，不在为0。重新开始在初始位置学习，个体通过查询保存的Q值知道位置$S_H$的价值较大，就可能移动到$S_G$位置。所以之后，所有能移动到$S_H$的位置的价值就会得到提升。如果纯粹使用贪婪策略，所有能到$S_G$的路径的最后一段都会从$S_H$向上走。而$\epsilon$-贪婪策略却会考虑其他可能的位置，造成结果每次的路线不同
**$\textrm{Sarsa}(\lambda)$算法**：维护了效用迹关于(S,A)的E表并初始化为0。当个体从起点$S_0$依策略选择动作$A_0$时得到TD误差即$\delta_t=0$，表示这个状态行为对对解决整个问题没有价值，同时在$E(S_0,A_0)$有个记录。直到得到回报1（当状态价值对为$(S_H,A_\textrm{向上})$时），所有路径上的(S,A)将会得到的Q值会得到非零更新，但个体到达$S_H$之前就近发生以及频繁发生的状态行为对的价值提升明显(E(S,A))。所以可以看出右边的图，离$S_H$越近，线越粗。如果需要找$S_0$到$S_H$的最短路径，只需要指定除终止状态的所有状态的回报为-1。
## 借鉴策略Q学习算法
### 借鉴策略的TD学习
设行为策略为$\mu(a|s)$，待目标策略（借鉴策略）为$\pi(a|s)$。前者可以从人类或者其他个体习得。借鉴学习是站在$\pi(a|s)$的角度上学习。

借鉴策略TD学习任务是使用TD方法在$\pi(a|s)$的基础上更新行为价值，进而优化策略：
$$V(S_t)\gets V(S_t)+\alpha(\frac{\pi(A_t|S_t)}{\mu({A_t}{S_t})}(R_{t+1}+\gamma V(S_{t+1}))-V(S_t))$$
借鉴策略与行为策略的比值：

- 接近1：表明二者选择相同行为$A_t$的概率差不多
- 大于1：借鉴策略选择行为$A_t$的概率大于当前行为策略选择的$A_t$
- 比值很小：借鉴策略$\pi$在状态$S_t$选择$A_t$的机会小

### Q学习
Q学习典型的行为策略$\mu$是基于行为价值函数Q(s,a)的$\epsilon$贪婪策略，借鉴策略$\pi$则是基于Q(s,a)的完全贪婪策略。
目标是得到最优价值Q(s,a)，在Q学习过程中，t时刻与环境进行交互的行为$A_t$由策略$\mu$产生：
$$A_t\sim\mu(\cdot|S_t)$$
t+1时刻用来更新Q值的行为$A^{'}_{t+1}$由下式产生：
$$A^{'}_{t+1}\sim\pi(\cdot|S_{t+1})$$
$Q(S_t,A_t)$按照以下的式子更新：
$$Q(S_t,A_t)\gets Q(S_t,A_t)+\alpha({\color{red}{R_{t+1}+\gamma Q(S_{t+1},A^{'})}}-Q(S_t,A_t))$$
红色字体部分的TD目标是基于借鉴策略$\pi$产生的行为$A^{'}$得到的Q值。这个就能保证状态$S_{t}$根据$\mu$策略得到的行为$A_t$的价值朝着$S_{t+1}$状态下$\pi$策略（完全贪婪策略）确定的最大的行为价值的方向进行一定比例的更新。

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/q_learning_example.png)
所以Q学习的具体的行为价值公式：
$$Q(S_t,A_t)\gets Q(S_t,A_t)+\alpha(R+\gamma\max_{a^{'}}Q(S_{t+1},a^{'})-Q(S_t,A_t))$$
Q学习的具体算法：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/q_learning_algorithm.png)
Sarsa 算法和Q学习的不同：每走一步的得到的回报是-1。掉进悬崖的回报是-100并回到初始的起点。

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/diff_between_qlearning_and_sarsa.png)
可以看到，Sarsa 算法总是找安全路线以保持与悬崖的最远距离，而Q学习由于状态价值是按照$\pi$策略更新的，所有也就总是会选择价值最大的动作。如果$\epsilon$策略中的$\epsilon$随着Episode的增加逐渐趋于0，那么二者都将最后收敛到最优策略。

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/diff_between_qlearning_and_sarsa2.png)
## 最后
TD和DP的一个总结（来自David Silver 的幻灯片）：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/model_free_control/relationship_between_dp_td.png)
# 参考&引用
- [(知乎)David Silver强化学习公开课中文讲解及实践-第五讲](https://zhuanlan.zhihu.com/p/28108498)
- [David Silver深度强化学习第5课 - 免模型控制](https://www.bilibili.com/video/av10131824)