# Policy Gradient
## 假设和问题陈述
按照之前的叙述, 一个完整的序列**轨迹**(trajectories)是一个完整的从开始到结束的状态的 episode 序列, 即 **episodic** 性. 定义长度为$T$的轨迹$\tau$:
$$\tau = (s_0, a_0, r_0, s_1, a_1, r_1, \ldots, s_{T-1}, a_{T-1}, r_{T-1}, s_T)$$
$s_0$通常来自于状态的分布$a_i\sim\pi_\theta(a_i|s_i)$, 当前环境采取 action 之后的状态$s_i\sim{P(s_i|s_{i-1},a_{a-1})}$

与之前的 DQN 采用求最优的 Q-values 不同间接求最优策略, 策略梯度直接计算最优策略. 公式化后:
$$\max_{\theta}\mathbb{E_{\pi_\theta}}\left[{\sum_{t=0}^{T-1}\gamma^tr_t}\right]$$
现在的问题就是如何求最优的策略, 即最好的参数$\theta$.
## 计算技巧
之前的文章我们给出了如何求$f(x)$的期望的梯度, 其中$p_\theta$是随机变量$x$的参数化后的概率密度函数:
$$\nabla_\theta\mathbb{E}[f(x)]=\mathbb{E}[f(x)\nabla_\theta\log{p_\theta(x)}]$$
在上面的公式中, 令$x$为$\tau$可以得到
$$\begin{aligned}
\nabla_\theta \log p_\theta(\tau) &= \nabla \log \left(\mu(s_0) \prod_{t=0}^{T-1} \pi_\theta(a_t|s_t)P(s_{t+1}|s_t,a_t)\right) \\
&= \nabla_\theta \left[\log \mu(s_0)+ \sum_{t=0}^{T-1} (\log \pi_\theta(a_t|s_t) + \log P(s_{t+1}|s_t,a_t)) \right]\\
&= \nabla_\theta \sum_{t=0}^{T-1}\log \pi_\theta(a_t|s_t)
\end{aligned}$$
$\tau$的概率就是 MDP 中下一个状态仅仅取决当前的状态.
## 计算原始梯度
定义$R(\tau)$为需要最大化的价值函数, 使用上述的两个技巧:
$$\nabla_\theta \mathbb{E}_{\tau \sim \pi_\theta}[R(\tau)] = \mathbb{E}_{\tau \sim
\pi_\theta} \left[R(\tau) \cdot \nabla_\theta \left(\sum_{t=0}^{T-1}\log
\pi_\theta(a_t|s_t)\right)\right]$$
当然平均价值函数也是可以使用的: 见

$\tau$中的状态来自于策略$\pi_\theta$, 也就是$\tau\sim\pi_\theta$. 实际上, 这是一个经验期望(empirical expectation)以估计实际的期望. 如果直接使用梯度下降得到最优参数不仅很慢还会带来在梯度估计上的**高方差(high variance)**, 得到结果时好时坏. 为了降低偏差有很多的方法, 引入 **baseline** 就是解决方案之一
## Baseline
# 参考
- https://danieltakeshi.github.io/2017/03/28/going-deeper-into-reinforcement-learning-fundamentals-of-policy-gradients/
- https://lilianweng.github.io/lil-log/2018/04/08/policy-gradient-algorithms.html
- https://danieltakeshi.github.io/2018/06/28/a2c-a3c/#fn:newblog