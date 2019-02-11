# DQN
DQN(Deep Q-)由 Google Deepmind 提出用于玩 Atair 系列的游戏以及 ALE 结构的游戏. DQN 的输入是一些像素点, 在过去这是很难被处理的. 同时一些强化学习的方法存在了一些挑战:
1. 深度学习的任务包含了大量的需要被标记的数据, 也就是说 RL 算法需要从那些标量的 Reward signal, 但是他们通常是**稀疏的**(很多 reward 都是 0), **有噪声的**和**延迟的**(与监督学习不同, action 和其造成的最终的回报的间隔时间很长(例如下棋)).
2. 深度学习算法是需要假设为独立的数据样本, 而在强化学习中状态可之间是有关系的(例如$S_{t+1}$是在$S_t$的状态下采取行动$a$转移到的).
3. DL 的目标分布固定; RL 的分布一直变化, 例如玩一个游戏,在一个关卡和下一个关卡的状态分布不同, 之前训练好的关卡又要重新训练.
4. 非线性网络表示值函数时, 出现不稳定等问题和发散(diverge)的问题

DQN 中解决这些问题的方法
- 通过 Q-learning 使用 reward 来构造标签(问题1)
- 为了解决相关的数据和不稳定分布(non-stationary distributions), 即问题2和3, 模型还引入了**经验回放机制**(experience replay mechanism).
- 使用 CNN(MainNet) 产生当前 Q值, 使用另一个 CNN(Target)产生 Target Q值.(问题4)
## 环境定义
agent 的交互环境为$\mathcal{E}$, 在时间$t$选择的行为 $a_t\in\mathcal{A}=\left\{1,\cdots,K\right\}$. 当前 agent 所处的状态是一个屏幕图像(即 observation)$x_t\in\mathbb{R}^d$, 当然, 一张图片无法使得 agent 了解到自己所处的状态(例如运动的方向以及速度), 所以我们定义一个 actions 和 observations 的序列
$$s_t=x_1,a_1,\cdots, a_{t-1},x_t$$
## Q-learning 的近似
我们对于 Q值的目标就是最大化折扣的累积回报:
$$Q^*(s,a)=\max_\pi\mathbb{E}[r_t+\gamma{r_{t+1}}+\gamma^2{r_{t+2}}+\cdots|s_t=s,a_t=a,\pi]$$
一个很好的稳定的方法是 Q迭代(Q-iteration), 这个方法在大空间上是低效的, 所以我们引入了一个参数向量$\boldsymbol{\theta}$来参数化Q, 记为$Q(s,a;\theta_i)$. 如下图, 这是一个输入图像为84x84x4的像素张量(已经过与处理函数$\phi$处理), 每个隐藏层的激活函数都是 ReLU.
.![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dqn/parameter_theta_in_nn.png)

如图所示, $\theta_i$可以认为是一个在迭代$i$处 Q-network 的权重, 为了执行经验回放, 我们必须把当前习得的经验$e_t=(s_t,a_t,r_t,s_{t+1})$存入到经验池中$D_t={e_1,\cdots,e_t}$. 在学习的过程中, 在 Q-learning 的更新中, 将会对经验池(experience pool)中(一小部分)的样本$(s,a,r,s')\sim{U(D)}$进行更新操作(原文: During learning, we apply Q-learning updates, on samples (or minibatches) of experience $(s,a,r,s')\sim{U(D)}$, drawn uniformly at random from the pool of stored samples.)
在每一次迭代$i$将使用损失函数:
$$\begin{equation}
L_i(\theta_i)=\mathbb{E_{(s,a,r,s')}}\left[{\left({r+\gamma\max_{a'}Q(s',a';\theta_i^-)-Q(s,a;\theta_i)}\right)^2}\right]
\label{loss_function_approx}
\end{equation}$$
其中$\theta_i^-$是用于计算在迭代$i$的 target-network 的参数, target-network 的参数仅仅的会在 Q-network 执行$C$步之后被更新, 并且在不同更新之间保持独立. 
## 网络结构
在 DQN 之前, 有的网络使用的是左图的结构, 输入是$S,A$输出$Q$值; DQN 采用的是右图的形式, 及输入$S$, 输出离线的各个 action 的 Q值. 前者的缺点在于对于每个 state, 需要计算$|A|$次前向计算, 而右图只需要一次前向计算即可:
.![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dqn/network_architecture.png)
## 算法 & 模型部分
### 图像预处理
Atair 2600的每一帧是 210x160 的 128位彩色图, 所以函数$\phi$将一帧图像$x_t$进行裁剪, 输出84x84的图像, 同时将最近的四帧作为按照栈的顺序压入和弹出, 构成张量$s_t$.
### 网络结构
主要是 CNN, 具体的详见论文. 这个不是重点.
### 算法
action 的空间已经定义为$\mathcal{A}$. 通常情况下, 环境$\mathcal{E}$是随机的(stochastic), 模拟器的内部状态是不可见的(有模拟系统控制实际的玩家), agent 智能观察图像. $r_t$ 将会反映在游戏得分的变化上, 有些游戏的分数取决于一系列的 actions 和 observations 序列之后. 所以这时候 action 的反馈有可能已经之后了很久了(time-steps have elapsed).

**POMDP**(部分观测的马尔可夫决策过程): 由于一帧画面并不能反应 agent 的状态, 所以我们使用了$s_t$ 来反应反应游戏的进行的状态. 这些序列(sequence, 指$s_t$)将会用于学习策略同时所有的序列将会在一个有限的 time step 终止, 所以就产生了巨大的有限马尔科夫决策过程(finite MDP).

类似于之前的$G_t$, 定义$R_t=\sum_{t'=t}^T{\gamma^{t'-t}r_{t'}}$. 最优的 action-value function 也就是 Bellman 方程: 如果状态$s'$采取动作$a'$的最优值$Q^*(s',a')$, 那么最优的策略就是选择动作$a'$以最大化$r+\gamma{Q^*(s',a')}$的期望:
$$Q^*(s,a)=\mathbb{E}_{s'}\left[{r+\gamma\max_{a'}{Q^*(s',a')}|s,a}\right]$$
如果使用诸如值迭代等方法, 最优值$Q_i\rightarrow{Q^*}\text{as }{i\rightarrow\infty}$, 不过我们使用的是对 action-value function 的估计, 即$Q(s,a;\boldsymbol{\theta})\approx{Q^*(s,a)}$.
#### Q-network
前面提到对 action-value function 的估计, 我们也可以使用像神经网络一样的非线性 approximator. 使用 Q-network, 将减小对于最优 action-value 函数估计的均方误差(使用 1-step TD error), 同时更新参数, 最优的输出预测目标(target) $r+\gamma\max_{a'}{Q^*(s',a')}$被替换为近似的 **target**, 其值为$y=r+\gamma\max_{a'}{Q(s',a';\theta_i^-)}$, $\theta_i^-$ 如之前的介绍. 损失函数就需要变为:
$$\begin{aligned}
    L_i(\theta_i)&=\mathbb{E_{s,a,r}}{\left[({\mathsf{E}\left[{y|s,a}\right]-Q(s,a;\theta_i)})^2\right]}\\
    &=\mathbb{E_{s,a,r,s'}}{\left[{(y-Q(s,a;\theta_i))^2}\right]+\mathsf{E}_{s,a,r}[\mathsf{V_{s'}}[y]]}
\end{aligned}
$$
这类似于$\eqref{loss_function_approx}$.

targets 依赖于网络的权重, 与监督学习的 targets 不同, 他们会在学习开始之前被固定. 在每一次优化的阶段, 当优化第$i$次迭代的损失函数$L_i(\theta_i)$, 算法会保存先前的在迭代$i$$参数\theta_i^-$固定. 最后一项是 target 的方差, 在求梯度的时候可以被忽略. 损失函数的梯度:
$$\nabla_{\theta_i} L_i(\theta_i)=\mathbb{E_{s,a,r,s'}}\left[{\left({r+\gamma\max_{a'}Q(s',a';\theta_i^-)-Q(s,a;\theta_i)}\right)}\nabla_{\theta_i}Q(s,a;\theta_i)\right]$$ 
Q-learning 的更新权重的方法也能够在每一个 time step 更新神经网络的权重(weights)同时设置$\theta_i^-\coloneqq{\theta_{i-1}}$. DQN 是一个:
- model-free: 并不依赖状态转移矩阵$P$以及显式估计 reward
- off-policy: 为了有机会探索整个空间(即探索和利用的权衡), 使用了$\epsilon-\text{greedy}$策略; 但选取行动则使用了$a=\arg\max_{a'}Q(s,a';\theta)$, 能使 Q值最大化的 action.
#### 训练 deep Q-networks
DQN 的算法的流程如下(Nature-2015):
.![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dqn/algo_dqn.png)
算法的一些细节:

**经验回放**: 在内循环中对于将 Q-learning 的更新或小批量的更新(minibatch updates)应用在经验池的一些样本(由分布$U$采样)中, 即$(s,a,r,s')\sim{U(D)}$, 可以:
1. 有些经验会多次在 Q-leanring 更新中被采样到, 提高了样本数据的使用的效率
2. 由于连续的(consecutive)的状态之间有关联, 随机化采样过程能够阻止这种相似数据的关联, 降低了更新之后带来的方差(variance)
3. 根据当前的证字啊学习的的策略以及参数能够决定当前参数的将要训练的那些样本(原文: Third, when learning on policy the current parameters determine the next data sample that the parameters are trained on.)

注意经验池的容量只有$N$, 超过的样本将会被丢弃或者采用一些替换算法(prioritized sweeping?)

**separate Target Network**: 原始 Q-learning 中, 在 1-step TD return, 样本标签$y$使用的是和训练的Q-network相同的网络. 这样通常情况下, 能够使得Q大的样本, $y$也会大, 这样模型震荡和发散可能性变大.而构建一个独立的慢于当前Q-Network的target Q-Network来计算$y$, 使得训练震荡发散可能性降低, 更加稳定.

**TD-error**: 即$r+\gamma\max_{a'}{Q(s',a';\theta_i^-)-Q(s,a;\theta_i)}$被 clip 到区间$[-1,1]$, 增加模型的稳定性.
#### 算法的整体思路
.![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dqn/proced_dqn.jpg)
#### 损失函数的构造
.![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dqn/loss_function.png)
## DQN 的优缺点
**优点**
- 算法通用性, 可玩不同的游戏.
- end-to-end 训练方式.
- 可生产大量样本供监督学习.

**缺点**
- 无法应用于连续动作控制.
- 只能处理只需短时记忆问题, 无法处理需长时记忆问题(后续研究提出了使用LSTM等改进方法).
- CNN不一定收敛, 需精良调参.

## 关于 DQN 的改进点
- Double Q-Network: 仿照 Double Q-leanring, 一个Q-network 用于选择动作, 另一个 Q-network 用于评估动作, 交替工作, 解决 upward-bias问题, 效果不错. 三个臭皮匠顶个诸葛亮么, 就像工作中如果有double-check, 犯错的概率就能平方级别下降. 论文 [Deep Reinforcement Learning with Double Q-learning](https://arxiv.org/pdf/1509.06461.pdf)
- 基于优先级的 replay 机制, replay 加速训练过程, 变相增加样本, 并且能独立于当前训练过程中状态的影响. 这个 replay 权重还是和 DQN error 有关. 论文 [PRIORITIZED EXPERIENCE REPLAY](https://arxiv.org/pdf/1511.05952.pdf)
$$|{r+\gamma\max_{a'}Q(s',a';\theta_i^-)-Q(s,a;\theta_i)}|$$
- Dueling network: 在网络内部把 $Q(s,a)$分解成 $V(s) + A(s, a)$, $V(s)$与动作无关, $A(s, a)$与动作相关, 是$a$相对$s$平均回报的相对好坏, 是优势, 解决 reward-bias 问题. RL中真正关心的还是策略的好坏, 更关系的是优势, 另外在某些情况下, 任何策略都不影响回报, 显然需要剔除. [ICML 2016 Best Paper：DUELING NETWORK ARCHITECTURES FOR DEEP REINFORCEMENT LEARNING](https://arxiv.org/pdf/1511.06581v2.pdf) . Dueling Network网络架构如下, Dueling Network把网络分成一个输出标量$V(s)$另一个输出动作上Advantage 值两部分, 最后合成Q值. 非常巧妙的设计, 当然还是 end-to-end 的, 效果也是 state-of-art. Advantage 是一个比较有意思的问题, A3C中有一个A就是Advantage.
.![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/dqn/dueling_network.png)
# 摘自&引用自
- [深度强化学习（Deep Reinforcement Learning）入门：RL base & DQN-DDPG-A3C introduction](https://zhuanlan.zhihu.com/p/25239682)
- [(CSDN) 草帽B-O-Y: 深度强化学习——DQN](https://blog.csdn.net/u013236946/article/details/72871858)
- [Human-level control through deep reinforcement learning[J]. Nature, 2015, 518(7540):529-533.](https://web.stanford.edu/class/psych209/Readings/MnihEtAlHassibis15NatureControlDeepRL.pdf)
- [Mnih V, Kavukcuoglu K, Silver D, et al. Playing Atari with Deep Reinforcement Learning[J]. arXiv: Learning, 2013.](https://arxiv.org/abs/1312.5602)
- [Sutton, R. S., Barto, A. G. (2018 ). Reinforcement Learning: An Introduction. The MIT Press.](http://incompleteideas.net/book/the-book-2nd.html)