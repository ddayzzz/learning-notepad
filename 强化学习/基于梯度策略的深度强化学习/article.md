# 基于策略梯度的深度强化学习
在行为空间规模大或者连续行为的情况下。直接进行策略学习：将策略看成是**状态和行为**的带参数的策略函数，通过建立恰当的目标函数、利用个体与环境进行交互产生的奖励来学习得到策略函数的参数。策略函数针对连续行为空间将可以直接产生具体行为值，进而绕过对状态的价值学习。
实际中，策略评估和优化是基于价值函数，优化策略函数更加准确地反应状态的价值。
## 基于价值函数学习的问题
### 重名情况
对于以下的环境：
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/features_in_the_same_name.png)
格子的状态由两个特征表示，分别代表北和南边界是否有墙。骷髅是一个惩罚状态，钱袋子则是由丰厚奖励的终止状态。对于最左侧的灰色格子，状态特征为(1,1)。根据贪婪原则，在这个位置上的个体只会采取向左或向右的一个行为。但是当采取向西的行为时候，他将进入状态特征为(1,0)的左上角的格子，这个状态的最优策略是向东。如此就发生了徘徊的情况。同时，如果采取向东又会进入具有相同的状态特征的右侧灰格子，也发生永久徘徊的现象。根本的原因是状态发生了**重名**的情况。
### 固定策略
基于价值学习对应的最优策略是确定性策略。很容易会被对手找到规律而被打败。在石头剪刀布游戏下，最优策略恰恰是随即策略。
### 解决
在基于策略的强化学习中，策略$\pi$可以被参数化：
$$\pi_\theta(s,a)=\mathbb{P}[a|s,\theta]$$
策略函数$\pi_\theta$确定了在给定的状态和一定参数设置下，采取任何可能行为的概率，是一个概略密度函数。同之前的介绍，可以设计一个关于$\theta$的目标函数$J(\theta)$，通过相应的算法来寻找最优参数。
## 策略目标函数
对于一个能够形成完整状态序列的交互环境来说，由于一个策略决定了个体与环境的交互，因而设计目标函数$J_1(\theta)$为使用策略$\pi_\theta$时初始状态价值，即初始状态收获的期望：
$$\begin{equation}J_1(\theta)=V_{\pi_\theta}(s_1)=\mathbb{E}_{\pi_\theta}[G_1]\label{policy_target_initial}\end{equation}$$
有些环境没有明确的起始和终止状态，个体与环境持续交互。在这种情况下可以使用平均价值：
$$J_\textrm{avV}(\theta)=\sum_sd^{\pi_\theta}(s)V_{\pi_\theta}(s)$$
或者每一个时间步的平均奖励：
$$J_\textrm{avR}=\sum_sd^{\pi_\theta}(s)\sum_a{\pi_\theta}(s,a)R_s^a$$
其中$d^{\pi_\theta}(s)$是基于策略$\pi_\theta$生成的马尔可夫链关于状态的**平稳分布（stationary distribution ）**。
与价值函数近似不同，策略目标函数的值越大代表策略越优秀，可以使用梯度上升来解最优参数：
$$\nabla_\theta{J(\theta)}=\begin{bmatrix}
\frac{\partial J(\theta)}{\partial\theta_1}\\
\vdots\\
\frac{\partial J(\theta)}{\partial\theta_n}
\end{bmatrix}$$
参数的更新：
$$\Delta\theta=\alpha\nabla_\theta{J(\theta)}$$
假设有一个单步马尔可夫决策过程，对应的强化学习问题是个体与环境产生一个行为交互一次即得到一个即时奖励$r=R_{s,a}$，并形成一个完整的状态序列，根据公式$\eqref{policy_target_initial}$，策略目标函数：
$$J(\theta)=\mathbb{E}_{\pi_\theta}[r]=\sum_{s\in S}d(s)\sum_{a\in A}\pi_\theta(s,a)R_{s,a}$$
对应的策略目标函数的梯度：
$$\begin{aligned}&\nabla_\theta J(\theta)=\sum_{s\in S}d(s)\sum_{a\in A}\nabla_\theta\pi_\theta(s,a)R_{s,a}\\
&=\sum_{s\in S}d(s)\sum_{a\in A}\pi_\theta(s,a)\nabla_\theta\log\pi_\theta(s,a)R_{s,a}\\
&=\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a)r]\end{aligned}$$
其中$\nabla_\theta\log\pi_\theta(s,a)$称为分值函数。存在如下的定理：对于任何可谓的策略函数$\pi_\theta(s,a)$以及三种目标策略函数：$J=J_1,J_\textrm{avV}\textrm{和}J_\textrm{avR}$任意一种，策略目标函数的梯度（策略梯度）都可以写成用分值函数表示的形式：
$$\nabla_\theta J(\theta)=\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a)Q_{\pi_\theta}(s,a)]$$
公式建立了分值函数建与策略梯度、价值函数之间的关系。通过两种常用的基于线性特征组合的策略，可以发现其在策略梯度强化学习的作用
### 策略梯度的数学解释$^{[1]}$
定义几个符号：
- $p(x;\theta)$：是参数化的概率分布函数。在证明的过程中简记为$p(x)$。令$z=\log_2(p(x))$
- $f(x)$：在$p(x;\theta)$分布下的分值函数
我们需要找到一个通过更新分布的参数$\theta$来提高采样的分值。
$$% <![CDATA[
\begin{align}
\nabla_{\theta} E_x[f(x)] &= \nabla_{\theta} \sum_x p(x) f(x) & \textrm
{definition of expectation} \\
& = \sum_x \nabla_{\theta} p(x) f(x) &\textrm{swap sum and gradient} \\
& = \sum_x p(x) \frac{\nabla_{\theta} p(x)}{p(x)} f(x) & \textrm{both multiply and divide by } p(x) \\
& = \sum_x p(x) \nabla_{\theta} \log p(x) f(x) & \textrm{use the fact that } \nabla_{\theta} \log(z) = \frac{1}{z} \nabla_{\theta} z \\
& = E_x[f(x) \nabla_{\theta} \log p(x) ] & \textrm{definition of expectation}
\end{align}%]]$$
使用$p(x;\theta)$作为采样的概率分布（可以是高斯分布）。对于每一次的采样，我们也可以评估分值函数$f$。$\nabla_\theta\log p(x;\theta)$是一个向量，其中梯度告诉我们提高$x$的概率的方向。换句话说，如果我们需要按照$\nabla_\theta\log p(x;\theta)$的方向推动(nudge)$\theta$那么我们可以发现，分配给$x$的新的概率轻微的提高。
### 应用于离散行为空间的 Softmax 策略
这个策略使用描述状态和行为的特征$\phi(s,a)$与参数$\theta$的线性组合来权衡一个行为发生的几率：
$$\pi_\theta\propto e^{\phi(s,a)^T\theta}$$
对应的分值函数：
$$\nabla_\theta\log\pi_\theta(s,a)=\phi(s,a)-\mathbb{E}_{\pi_\theta}[\phi(s,\cdot)]$$
分值越高意味着在当前策略下对应行为被选中的概率越大，但是可能与最优行为（即时回报大）不同，当二者的行为不同的时候，策略调整应该使得选择奖励越大的行为出现的概率变大。**因此将结合某一行为的分值对应的奖励来得到对应的梯度，并以此基础调整参数，最终使得奖励越大的行为对应的分值越高**
### 应用于连续行为空间的高斯策略
该策略从高斯（正态）分布$\mathbb{N}(\mu(s),\sigma^2)$中产生。其均值$\mu(s)=\phi(s)^T\theta$。高斯策略的分值函数：
$$\nabla\log\pi_\theta(s,a)=\frac{(a-\mu(s))\phi(s)}{\sigma^2}$$
分值函数的形式$^{[1]}$：
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/policy_gradient_gaosi_policy.png)
**左图中**：蓝色的点代表在高斯分布下的采样点。针对每一个蓝点，画出了根据高斯分布均值得到的概率对数的梯度。箭头指示的方向是能够增加该采样点采样概率的分布的均值（对于高斯分布来说，是等值线的中心点）移动的方向。
**中图中**：大多数采样点对应的分值函数值是-1，除了一小块区域是+1（分值函数可以是任意、并且不要求可微的标量(scalar-valued)函数），此时箭头用不同颜色表示，在随后的更新中，我们将要把所有绿色的值和负的红色值进行平均来更新分布参数（均值）
**右图中**：参数更新后，绿色箭头的方向和红色箭头的反方向推动了行程均值朝着左下方移动的新的高斯分布，从这个新分布的采样将会按照预期有一个较高的分值。
> 原始的描述：A visualization of the score function gradient estimator. Left: A gaussian distribution and a few samples from it (blue dots). On each blue dot we also plot the gradient of the log probability with respect to the gaussian's mean parameter. The arrow indicates the direction in which the mean of the distribution should be nudged to increase the probability of that sample. Middle: Overlay of some score function giving -1 everywhere except +1 in some small regions (note this can be an arbitrary and not necessarily differentiable scalar-valued function). The arrows are now color coded because due to the multiplication in the update we are going to average up all the green arrows, and the negative of the red arrows. Right: after parameter update, the green arrows and the reversed red arrows nudge us to left and towards the bottom. Samples from this distribution will now have a higher expected score, as desired.

对于连续行为空间中的每一个行为特征，由策略$\pi(\theta)$产生的行为对应的该特征分量都服从一个高斯分布，该分布中采样得到一个具体行为分量，多个行为分量整体形成一个行为。采样得到的不同行为对应于不同的奖励。参数$\theta$的调整方向是用一个新的高斯分布去拟合使得那些得到正向奖励的行为值和负向奖励的行为值的相反数形成的采样结果。最终基于新分布的采样结果集中在那些奖励值高的行为上。
## Actor-Critic 算法
Actor：可以理解为策略函数，生成行为与环境交互；Critic：行为价值函数，负责Actor的表现，并指导后续行为动作。Crtic的行为价值函数是基于策略$\pi_\theta$的一个近似：
$$Q_w(s,a)\approx Q_{\pi_\theta}(s,a)$$
基于此，Actor-Critic 算法遵循一个近似的策略梯度进行学习：
$$\nabla_\theta J(\theta)\approx\mathbb{E}[\nabla_\theta\log\pi_\theta(s,a)Q_w(s,a)]\\
\Delta\theta=\alpha\nabla_\theta\log\pi_\theta(s,a)Q_w(s,a)$$
由于Critic算法也是带有参数w的，所以也需要学习以便更准确地评估一个策略。一个最基本地基于行为价值Q地Actor-Critic算法：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/qac_algorithm.png)
由于Critic还是一个近似值函数，存在引入偏差的可能。
**定理**：如果满足：

1. 近似价值函数的梯度与分值函数的梯度相同，即$\nabla_wQ_w(s,a)=\nabla_\theta\log\pi_\theta(s,a)$
2. 近似价值函数的参数w能够最小化$\epsilon=\mathbb{E}_{\pi_\theta}[(Q_{\pi_\theta}-Q_w(s,a))^2]$

那么策略梯度$\nabla_\theta J(\theta)$是准确的，即：
$$\nabla_\theta J(\theta)=\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a)Q_w(s,a)]$$
使用$Q_w(s,a)$来计算策略目标的梯度有时候会发生数据过大的情况原因在于行为价值本身有较大的变异性(variance)。为了解决这个问题，提出一个**与行为无关的仅基于状态的基准函数(baseline)函数$B(s)$**，要求$B(s)$满足：
$$\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a)B(s)]=0$$
可以将基准函数从策略梯度中提取出来减少变异性且**不改变基准函数的期望**，而基于状态的价值函数$V_{\pi_\theta}(s)$函数就是一个不错的基准函数。令**优势函数(advantage function)**为：
$$A_{\pi_\theta}(s,a)=Q_{\pi_\theta}(s,a)-V_{\pi_\theta}(s)$$
表达了在策略$\pi_\theta$下状态s选择动作a有多好。如果a比平均的价值要好，那么优势函数的值就为非负。其中$V_{\pi_\theta}(s)$定义为：
$$V_{\pi_\theta}(s)=\mathbb{E}_{\pi_\theta}[G_t|s_t=s]$$
是一累积的的奖励的均值。
策略目标函数梯度可以描述为：
$$\nabla_\theta J(\theta)=\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a)A_{\pi_\theta}(s,a)]$$
优势函数相当于记录了在状态s时采取的行为a会比停留在状态s多出的价值，由于优势函数考虑的是价值的增量，因而大大减少的策略梯度的变异性，提高算法的稳定性。在引入优势函数后，Critic函数可以仅是优势函数的价值近似。由于基于真实价值函数$V_{\pi_\theta}$的TD误差$\delta_{\pi_\theta}$就是一个无偏估计（待估计参数的估计量的期望值等于参数本身）：
$$
\begin{aligned}
& \mathbb{E}_{\pi_\theta}[\sigma_{\pi_\theta}|s,a]=\mathbb{E}_{\pi_\theta}[r+\gamma V_{\pi_\theta}(s^{'})|s,a]-V_{\pi_\theta}(s)\\
&= Q_{\pi_\theta}(s,a)-V_{\pi_\theta}(s)\\
&=A_{\pi_\theta}(s,a)
\end{aligned}
$$
所以不需要定义两个参数来近似优势函数（按照定义，优势函数有两个函数做差）。因此又可以使用TD误差来计算策略梯度：
$$\nabla_\theta J(\theta)=\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a)\delta_{\pi_\theta}]$$
实际中，使用带参数w的近似价值函数$V_w(s)$来近似TD误差：
$$\delta_w=r+\gamma V_w(s^{'})-V_w(s)$$
只用一个参数w来描述Critic。
使用不同的强化学习方法来进行Actor-Critic学习时：
方法|描述Critic的函数$V_w(s)$的参数w可以通过的更新形式
-|-
MC|$\Delta w=\alpha({\color{red}{G_t}}-V_w(s))\phi(s)$
TD(0)，红色部分为TD目标|$\Delta w=\alpha({\color{red}{r+\gamma V(s^{'})}}-V_w(s))\phi(s)$
前向$\text{TD}(\lambda)$，目标是$\lambda$-收获($\lambda$-return)|$\Delta w=\alpha({\color{red}{G_t^\lambda}}-V_w(s))\phi(s)$
后向$\text{TD}(\lambda)$，使用效用迹|$\delta_t=r_{t+1}+\gamma V_w(s_{t+1})-V_w(s_t)\\e_t=\gamma\lambda e_{t-1}+\phi(s_t)\\\Delta w=\alpha\delta_t e_t$
类似的，策略梯度：
$$\nabla_\theta J(\theta)=\mathbb{E}_{\pi_\theta}[\nabla_\theta\log\pi_\theta(s,a){\color{red}{A_{\pi_\theta}(s,a)}}]$$
也可以使用不同的学习方法更新策略函数$\pi_\theta(s,a)$的参数$\theta$：

1. MC：$\Delta\theta=\alpha(\color{red}{G_t}-V_w(s_t))\nabla_\theta\log\pi_\theta(s_t,a_t)$
2. TD(0)：$\Delta\theta=\alpha(\color{red}{r+\gamma V_w(s_{t+1})}-V_w(s_t))\nabla_\theta\log\pi_\theta(s_t,a_t)$
3. 前向$\text{TD}(\lambda)$：$\Delta\theta=\alpha(\color{red}{G_t^\lambda}-V_w(s_t))\nabla_\theta\log\pi_\theta(s_t,a_t)$
4. 后向$\text{TD}(\lambda)$，这个更新可以是在线(online)基于不完整的状态序列：$$\delta_t=r_{t+1}+\gamma V_w(s_{t+1})-V_w(s_t)\\e_t=\gamma\lambda e_{t-1}+\nabla\log\pi_\theta(s_t,a_t)\\\Delta\theta=\alpha\delta_t e_t$$

基于TD的Actor-Critic算法的结构：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/actor_critic_architecture.png)
最后总结策略梯度算法：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/summary_policy_gradient.png)
## 异步强化学习算法$^{[5][6]}$
### 并行(Parallel)的个体
如果采用单个个体与环境交互，那么得到的样本就有可能是高度相关的，这会使得机器学习的模型出现问题。因为机器学习的条件是：采样(sample)满足独立同分布，但是不能高度相关。在DQN中可以使用经历回访来克服这个问题。但是这样子就引入了借鉴学习策略(off-policy)，需要存储采样以及行为价值Q。
### Asynchronous Advantage Actor-Critic(A3C)算法
定义策略：
$$\pi(a_t|s_t;\theta)$$
已经值函数：
$$V(s_t|\theta_v)$$
可以把上面式子中的$\theta_v$看成之前介绍的值函数的参数w。
与使用n-步返回的Q学习，A3C算法中也使用n-步返回，不过n-返回的缺点就是方差高，可能不会收敛。

在一个Episode中，时间范围$t_{\text{max}}$n内或者达到终点状态的情况下，更新的目标：
$$\nabla_{\theta^{'}}\log\pi(a_t|s_t')A(s_t,a_t;\theta,\theta_v)$$
，其中优势函数$A(s_t,a_t;\theta,\theta_v)$的定义为：
$$\sum_{i=0}^{k-1}\gamma^it_{t+i}+\gamma^k V(s_{t+k};\theta_v)-V(s_t;\theta_v)$$
算法的具体流程：
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/a3c_algorithm.png)
虽然策略的参数$\theta$和值函数参数$\theta_v$不同，但是在实际上他们可以看作是一个参数即在上述的AC算法原始的优势函数中的参数$\theta$。所以，按照论文[6]中的叙述，把策略和值函数放在含有一个softmax输出和一个线性输出的CNN网络中：
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/policy_and_value_function_in_the_same_cnn.png)
我们发现通过在策略$\pi$添加信息熵(entropy)到目标函数可以通过放弃(discouraging)过早地收敛到局部最优确定性策略(suboptimal deterministix policies)来改进探索。最终完整的目标函数被修改为：
$$\nabla_{\theta^{'}}\log\pi(a_t|s_t;\theta^{'})(G_t-V(s_t;\theta_v))+\beta\nabla_{\theta^{'}}H(\pi(s_t;\theta^{'}))$$
，其中H是信息熵。超参数(hyperparameter)$\beta$控制了h熵正则化的长度(entropy regularization term)。
### 实现的解释
我使用了别人基于环境DeepMind Lab的代码[7]并做了相关的注释（[Github](https://github.com/ddayzzz/rl_deepmindlab/blob/master/agent_a3c.py)），至于DeepMind Lab的相关环境可以参考论文[8]以及官方软件库[9]。
未完待续。

A3C算法实现各个组件以及工作的流程：
![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/policy_gradient/whole_implementation_of_a3c.png)
## 深度确定性策略梯度(DDPG)算法
未完待续
# 补充：平稳分布
平稳分布是[马尔可夫链](https://en.wikipedia.org/wiki/Markov_chain)上的随着时间推移不会改变的概率分布。通常被表示为行向量$\pi$，每个元素(entries)的和是1，满足：
$$\pi=\pi \textbf{P}$$
[Ergodic Markov chains](https://brilliant.org/wiki/ergodic-markov-chains/)有唯一的平稳分布[Absorbing Markov Chains](https://brilliant.org/wiki/absorbing-markov-chains/)可能有多个元素非负的平稳分布。平稳分布刻画的是随机过程的稳定性(stability)，在有些情况下也能够表示马尔可夫的极限特征(?limiting behavior)
与特征值和特征向量(eigenvalues ans eigenvectors)的定义相似$Mv=\lambda v$，其中$\lambda=1$，事实上通过转置：
$$(\pi\textbf{P})^T=\pi^T\implies\textbf{P}^T\pi^T=\pi^T$$
由于$\textbf{P}^T$的特征值为1的特征向量（实际上是平稳分布的列向量的表示形式）。如果$\textbf{P}^T$的特征向量已知的话，状态转移矩阵为$\textbf{P}$的马尔可夫链的平稳分布也能够知道。**平稳分布是状态转移矩阵的左特征向量**。
如果马尔可夫有多个状态，也就是马尔可夫链可约(reducible)的话，可能与特征值为1的特征向量有多个，每一个特征向量都会引起(gives rise to)响应的平稳分布。
假设马尔可夫有三个状态，状态转移矩阵：
$$\textbf{P}=\begin{bmatrix}
0.5 & 0.5 & 0\\
0.25 & 0.5 & 0.5\\
0 & 0.5 & 0.5
\end{bmatrix}$$
按照求解特征向值和特征向量的步骤：
$$\det(\textbf{P}-\lambda\textbf{I})=\det\begin{bmatrix}
0.5-\lambda & 0.25 & 0\\
0.5 & 0.5-\lambda & 0.5 \\
0 & 0.25 & 0.5-\lambda
\end{bmatrix}=0$$
按照3阶行列式的三角形展开：$(0.5-\lambda)^3-2\times0.125(0.5-\lambda)=(0.5-\lambda)(\lambda^2-\lambda)=0$。可以解得：
特征值|特征向量
-|-
0|(1,-2,1)
1|(1,2,1)
0.5|(-1,0,1)
最终可能的静态分布要求特征向量的元素非负：
$$\pi=\frac{1}{1+2+1}(1,2,1)=\left(\frac{1}{4},\frac{1}{2},\frac{1}{4}\right)$$
# 参考&引用
1. [Deep Reinforcement Learning: Pong from Pixels](http://karpathy.github.io/2016/05/31/rl/)
2. [(知乎)David Silver强化学习公开课中文讲解及实践-第七讲](https://zhuanlan.zhihu.com/p/28348110)
3. [(知乎)什么是无偏估计？](https://www.zhihu.com/question/22983179)
4. [Stationary Distributions of Markov Chains](https://brilliant.org/wiki/stationary-distributions/)
5. [LET’S MAKE AN A3C: THEORY](https://jaromiru.com/2017/02/16/lets-make-an-a3c-theory/)
6. Volodymyr Mnih, Adrià Puigdomènech Badia, Mehdi Mirza, Alex Graves, Timothy P. Lillicrap, Tim Harley, David Silver, Koray Kavukcuoglu. Asynchronous Methods for Deep Reinforcement Learning. [arXiv:1602.01783](http://arxiv.org/abs/1602.01783), 2016.
7. [(Github)avdmitry:rl_3d](https://github.com/avdmitry/rl_3d)
8. Charles Beattie, Joel Z. Leibo, Denis Teplyashin, Tom Ward, Marcus Wainwright, Heinrich Küttler, Andrew Lefrancq, Simon Green, Víctor Valdés, Amir Sadik, Julian Schrittwieser, Keith Anderson, Sarah York, Max Cant, Adam Cain, Adrian Bolton, Stephen Gaffney, Helen King, Demis Hassabis, Shane Legg, Stig Petersen. DeepMind Lab. [arXiv:1612.03801](https://arxiv.org/abs/1612.03801), 2016.
9. [(Github)DeepMind Lab](https://github.com/deepmind/lab)
