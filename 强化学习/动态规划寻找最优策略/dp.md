# 动态规划寻找最优策略
动态规划问题中一般都有：

- 重叠的子问题
- 通过解决子问题可以得到整个问题的解
马尔可夫决定过程（MDP）具有上述的两个属性。

预测和控制是规划的两个重要的内容。预测是对给定的策略的评估过程；控制是寻找一个最优策略的过程。

- 预测：已知一个马尔可夫决策过程 MDP $<S,A,P,R,\gamma>$和一个策略$\pi$，或者是一个马尔可夫奖励过程MRP$<S,P_\pi,R_\pi,\gamma>$，求解基于该策略的价值函数$v_\pi$
- 控制：已知一个马尔可夫决策过程 MDP $<S,A,P,R,\gamma>$，求最优价值函数$v_*$和最优策略$\pi$
## 策略评估
计算给定策略下状态价值函数的过程。贝尔曼期望方程给出了如何根据状态转换关系中后续状态$s^{'}$来结算当前状态$s$的价值，在同步迭代法中，使用上一个周期$k$内的后续状态价值来计算当前迭代周期$k+1$内某状态$s$的价值：
$$
\begin{equation}
v_{k+1}(s)=\sum_{a\in A}\pi(a|s)(R_s^a+\gamma\sum_{s^{'}\in S}P_{ss^{'}}^av_k(s^{'}))
\end{equation}
$$
假设一个一个4x4的方格网络，只能上下左右移动一次，0和15状态都是终止状态。如果个体打算跳出格子，个体会有100%的几率保持不动。每移动一次，环境给予的回报：-1。简单起见，损失因子$\gamma=1$

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dp_mc_td_learning/grid_network.png)
由于个体并不清楚整个环境的特征，所以我们的个体需要不断的试探，而且他朝四个方向移动的概率是相等的为$\frac{1}{4}$，这个平均的策略是基于**均一的随机策略**。在这个策略的指导下，每一个状态的价值是不一样，对于个体来说，他需要经过多次的终止状态之后才能对各个状态的价值有一定的认识，这个人是的过程就是**策略评估的过程**。

详细的计算过程：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dp_mc_td_learning/value_iter_network_grid.png)

- 在图(b)中：4状态，根据公式：$v_1(s_4)=3\times0.25\times((-1)+1\times(0))+0=-1$，乘以4是因为除了向左的三个方向计算过程相同，向左出去了所以还是以1的概率保持不动，得到的值是$v_0(s_4)=0.0$。
- 在图(c)中：4状态，$v_2(s_4)=2\times0.25\times(-1+(-1\times1))+0.25\times(-1+1\times(-1))+0.25\times(-1+0)=-1.75\approx-1.7$。第一项是因为向下和向右的计算步骤是一样的。第二项是向左移动，得到的值函数是$v_1(s_4)=-1.0$，也就是保持不动。第三项是向上的得到的价值是0。注意由于我明确了选择的行为，所以转移概率是$P_{ss^{'}}^a=1$。
## 策略改善
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dp_mc_td_learning/greedy_prolicy.png)
当k=2，4状态的时候，个体可以向上移动以获取最好的回报而不是以随机策略来选择下一个目标。从一个随机的策略中产生一个更好的策略就是策略改善的过程。给定一个策略$\pi$，可以基于这个策略的价值函数$v_\pi$，基于这个价值函数可以得到一个贪婪策略$\pi^{'}=\textrm{greedy}(v_\pi)$。依据这个策略$\pi^{'}$会得到新的价值函数，并产生新的贪婪策略，然后重复迭代最终将得到最优价值函数$v^*$和最优策略$\pi^*$

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dp_mc_td_learning/policy_iteration.png)
## 价值迭代
如果迭代的次数很多而且最终得到的最优策略和中间某次的某个策略相同的话，我们可以减少迭代次数而不影响最终得到的最优策略。

任何一个阶段的最优策略：

- 这个策略产生的最优行为
- 该行为到达的后续状态仍然是一个最优策略。
之前的贝尔曼方程可以描述这种行为：
$$
\begin{equation} 
v^*(s)=\max_a(R_s^a+\gamma\sum_{s^{'}\in S}P_{ss^{'}}^av^*(s^{'}))\\ 
\label{optimal_state_value_bellman} 
\end{equation}
$$
如果能够知道最终的价值和相关的奖励，可以直接计算前一个所有可能的最优价值。以网格中的最短路径作为演示。假设左上角的格子是终止状态，初始的价值是0。在$V=1$的时候全部初始化所有状态值（根据$V=0$的终止状态的价值）

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dp_mc_td_learning/grid_network_shortestpath.png)
当$V=3$的时候，除了与终止状态相邻的两个状态，其他的状态的价值都将因采用一个行为获得的-1的回报以及前次迭代中$R_s^a=-1$而被更新为-2。当价值出现了-6的时候即$V=7$再次迭代不会出现了变化，于是整个迭代的过程便结束了。

迭代中价值函数的更新公式为：$$v_{k+1}(s)=\max_{a\in A}(R_s^a+\gamma\sum_{s^{'}\in S}v_k(s^{'}))$$
