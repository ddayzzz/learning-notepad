# 强化学习算法 - Tabular Solution Methods
## RL 的基本设定
关于马尔可夫经典的四元组, 可以从这篇文章获取. 强化学习的重要概念(这里主要是 Finite Markov Decision Processes):

**Policy**: 如果策略是随机的, policy 根据在状态$s$的动作概率$\pi(a|s)$选择动作; 如果策略是确定的, policy 直接根据状态$s$选择动作$a=\pi(s)$. 他们都需要满足:
- 随机策略(stochastic policy): $\sum\pi(a|s)=1$
- 确定性策略(deterministic policy): $\pi(s)=P[A_t=a|S_t=t]:S\rightarrow{A}$.

符号|含义
-|-
$t$|时间点(time step), $t=1,2,\cdots$
$S_t\in\mathcal{S}$|$\mathcal{S}$是状态集合, $S_t$是时刻$t$的状态, $s$是其中的某个特定的状态
$A_t\in{\mathcal{A}(S_t)}$|$\mathcal{A}$是状态$S_t$下的 actions 的集合, $A_t$是时刻$t$的 action, $a$代表某个特定的行为

**Reward Signal**: 定义了 agent 学习的目标. agent 每次和环境交互, 环境返回一个 **数值型(numerical)** reward, 高速 agent 刚刚的 action 是好是坏, 可以理解为对 agent 的奖励或者惩罚. agent 与环境交流的序列(trajectory)为:
$$S_0,A_0,R_1,S_1,A_1,R_2,\cdots,S_{n-1},A_{n-1},R_n$$
其中$S_{i-1},A_{i-1},R_i$被认为是在同一时间的一组交互的数据, 分别为 state, action 和 reward, $S_n$是终止状态. 这个序列在之后的 A3C 的部分中被记为$\tau$, 表示一个 episode 中 agent 与环境交互的序列.

**Model of the Environment**: MDP 中的 agent 与环境的交互过程:

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/summary_of_tabular/agent_environment.png)

$S_t$, $A_t$的含义见上表, $R_t\in\mathcal{R}\in\mathbb{R}$是数值型的 reward.
在每个事件步中, agent 都会实现一个从 states 到每个可能的 actions 的 probabilities 对应的映射, 称为这个 agent 的 policy, 记为$\pi_t$, $\pi_t(a|s)$指的就是在状态$S_t=s$下执行$A_t=a$的概率. 不同的强化学习的方法主要的不同就是利用**experience 来改变自己对$\pi_t$的方法**.
## Goals 和 Rewards
在时间步$t$的 reward 是即时的, 由**环境**返回一个数值(scalar signal). agent 的目标就(goals)是最大化他所接受的所有 reward 的和, 他的目的不是最大化当前的 reward, 而是长期的累积回报(cumulative reward in the long run). 设计 reward 不能考虑一个局部的(短期)的 reward 最大化, 例如棋类游戏, agent 不能以输棋的代价去奖励那些采取某些行动去实现局部目标(subgoals)的 action.
## Returns 和 Episodes
agent 的目标就是最大化所接受到的所有的 reward signal 的和, 即$G_t$, 令时间$t$获得的 reward 为 $R_{t+1},R_{t+2},\cdots$. 最简单的定义为:
$$\begin{equation}
G_t\doteq{R_{t+1}+R_{t+2}+\cdots{R_T}}
\label{simplest_gt}
\end{equation}$$
$T$是最后一个时间步.

**Episode**: 每一个 episode 都会在一个名为终止状态(terminal state)下结束. 采用 episodes 形式的 task 被认为是 episodic tasks. 每个 episode 都会以终止状态结束, 不同点就是每个 episode 的结果是不同的. 同时新的 episode 与前面结束的 episode 都无关. 在 episodic tasks 中, 将所有非终止状态记为$\mathcal{S}$, **包含**终止状态的所有状态集合记为$\mathcal{S}^+$
如果是是连续性任务(continuing tasks), 即不会自然结束持续的任务, 即公式$\eqref{simplest_gt}$中$T=\infty$.

**Discounting**: 在$\eqref{simplest_gt}$中, 可以对未来不同时刻的 reward 赋予不同的权重, 距离较近的 reward 的权重较高, 较远的权重越低, 此时选择行为$A_t$的准则就是最大化期望的 discounted return:
$$G_t\doteq{R_{t+1}+\gamma R_{t+2}+\gamma^2{R_{t+3}}+\cdots=\sum_{k=0}^\infty\gamma^kR_{t+k+1}}$$
$0\leq\gamma\leq{1}$是折扣率(discount rate), 它嗲表未来第$k$步的 reward 的价值只是**在当前**获得这个 reward 的 $\gamma^{k-1}$倍. 若$\gamma{<1}$, 上面的公式就会从在本发散的和变为前提为序列$\left\{R_k\right\}$的前提下转变为一个有限的值;若$\gamma=0$, agent变得目光短浅(myopic), 仅仅选择最大化$R_{t+1}$的动作;若$\gamma\rightarrow{1}$, 这个 agent 变得很"有远见".
## Value Function
Reward Signal 定义的是一次交互中的立即的(immediate sense)回报的好坏, 而 Value Function 定义了在长期的收益, 可以看作是累积的 Reward. 一般用$v$来表示, 为了获取当前状态的价值, 定义$v_\pi$(**state-value function of the policy $\pi$**):
$$v_\pi(s)\doteq{\mathbb{E}_\pi[G_t|S_t=s]=\mathbb{E}_\pi\left[{\sum_{k=0}^\infty\gamma^kR_{t+k+1}}|S_t=s\right]}, \quad\forall{s\in\mathcal{S}}$$
在书中, 终止状态的 reward 无特殊说明都是0.

相似地, 定义按照策略$\pi$在状态$s$下采取行动$a$的**action-value function for policy $\pi$**:
$$q_\pi(s,a)\doteq{\mathbb{E}_\pi[G_t|S_t=s,A_t=a]=\mathbb{E}_\pi\left[{\sum_{k=0}^\infty\gamma^kR_{t+k+1}}|S_t=s,A_t=a\right]}$$
同时书中定义, $Q$和$Q_t$分别表示总体估计(array estimates?)的 action-value function $q_\pi$和$q_*$;$V$和$V_t$分别表示总体估计的 state-value function $v_\pi$和$v_*$.

通常**估计**最优(optimal)函数$v_*$和$q_*$都可以用多中方法得到, 例如**基于模型的**动态规划(DP)以及**基于MDP, 但是不掌握具体 MDP 细节的**蒙塔卡洛(MC)方法和在时间差分(TD)等基于表(tabular)等方法, 具体可参见![强化学习）- 动态规划寻找最优策略和不基于模型的学习]目前主流都使用了结合了蒙特卡洛方法和动态规划方法的时间差分方法: 它不需要 MC 中回退(backup)整个序列更新$Q$, 只需要回退1步或者n步更新$Q$:
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/summary_of_tabular/mc_td.png)

为了**优化**最优的策略, 相关的方法有其对应的操作方法, 相关细节可以参考!

但是基于表的方法不适合解决大空间的强化学习的问题. 所以引入了各种价值函数的近似表示和学习(待续)
# 引用
- [深度强化学习（Deep Reinforcement Learning）入门：RL base & DQN-DDPG-A3C introduction](https://zhuanlan.zhihu.com/p/25239682)
- [3 有限马尔可夫决策过程（Finite Markov Decision Processes）](https://blog.csdn.net/coffee_cream/article/details/60473789)
- [Sutton, R. S., Barto, A. G. (2018 ). Reinforcement Learning: An Introduction. The MIT Press. ](http://incompleteideas.net/book/the-book-2nd.html)

