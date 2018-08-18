# 值函数近似
之前介绍的集中方法由于需要保存诸如效用迹、Q值表等信息，就会造成在拥有大量的状态的环境下存储空间不足的问题。

引入近似价值函数，不论是预测还是控制问题，都将转变成近似函数的设计及求解近似函数的参数这两个问题了。
## 价值近似
假设个体可以在一个二维空间中可以采取四种动作：水平和垂直方向的力。环境赋予这个空间某种**连续特征**，例如长度，如果空间的长度是单位1的话，那么个体的水平坐标在范围就是[0,1]内的连续变量。如果需要确定Q的值，就必须直到每个坐标对应的状态，这就可能需要对长度做某种等分，但是精度无法控制。如果精度大，那么水平方向的状态数量就可能大得惊人，普通的计算机可能无法存储这些状态。

如果能建立一个函数$\hat{v}$，由参数w描述，它可以直接接受便是状态特征的连续变量s作为输入，通过接收得到一个状态的价值，通过调整参数w的取值，使得其符合基于某一个策略$\pi$的最终价值，那么这个函数就是状态价值$v_{\pi}(s)$的表示：
$$\hat{v}(s,w)\approx v_\pi(s)$$
类似的，如果由参数w构成的函数$\hat{q}$接收状态变量s和行为变量a：
$$\hat{q}(s,a,w)\approx q_\pi(s,a)$$
针对行为空间的每一个离散行为的价值，状态s可以不仅仅是索引也可以是矩阵、张量等。三种价值函数近似：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/value_function_approx/three_different_value_func_approximation.png)
## 目标函数与梯度下降
现在的目标就是求解参数w
### 目标函数
之前的强化学习算法对状态行为价值的更新：
$$Q(S,A)\gets Q(S,A)+\alpha(R+\gamma Q(S^{'},A^{'})-Q(S,A))$$
不同的算法体现在目标值$R+\gamma Q(S^{'},A^{'})$的选取上，如果价值函数最终收敛不再更新，那么对任何状态或状态行为对，其目标价值与价值相同。

- 对于预测问题：收敛得到的Q就是基于某策略得到的**最终价值函数**
- 对于控制问题：收敛得到的价值函数也对应于**最优策略**

基于近似价值函数的价值更新：
$$\hat{Q}(S,A)\gets \hat{Q}(S,A)+\alpha(R+\gamma \hat{Q}(S^{'},A^{'})-\hat{Q}(S,A))$$
假设找到参数使得价值函数不再更新，下面的式子成立：
$$\hat{Q}(S,A,w)=R+\gamma\hat{Q}(S^{'},A^{'},w)$$
事实上很难找到完美的参数w使上式成立，而且算法基于采样未必对所有的可能的状态转换成立。设M为采样得到的状态转换总数，定义目标函数$J_w$：
$$J(w)=\frac{1}{2M}\sum_{k=1}^M[(R_k+\gamma\hat{Q}(S^{'}_k,A^{'}_k,w))-\hat{Q}(S_k,A_k,w)]^2$$
近似价值函数$\hat{Q}(S,A,w))$收敛，代表$J(w)$减小。成目标函数J为**代价函数(cost function)**。如果只有一个时刻t的状态转换，则称**损失函数**，定义为：
$$\textrm{loss}(w)=\frac{1}{2}[(R_t+\gamma\hat{Q}(S^{'}_t,A^{'}_t,w))-\hat{Q}(S_t,A_t,w)]^2$$
强化学习算法|代替的目标价值
-|-
MC学习|$$J(w)=\frac{1}{2M}\sum^M_{t=1}[G_t-\hat{V}(S_t,w)]^2\\J(w)=\frac{1}{2M}\sum^M_{t=1}[G_t-\hat{Q}(S_t,A_t,w)]^2$$
对于TD(0)和反向认识$\textrm{TD}(\lambda)$，使用TD目标代替目标价值|$$J(w)=\frac{1}{2M}\sum_{t=1}^M[R_t+\gamma\hat{V}(S^{'}_t,w)-\hat{V}(S_t,w)]^2\\J(w)=\frac{1}{2M}\sum_{t=1}^M[R_t+\gamma\hat{Q}(S^{'}_t,A^{'}_t,w)-\hat{Q}(S_t,A_t,w)]^2$$
对于前向认识$\textrm{TD}(\lambda)$，使用$G^\lambda$或$q^\lambda$代替目标价值|$$\begin{equation}J(w)=\frac{1}{2M}\sum^M_{t=1}[G_t^\lambda-\hat{V}(S_t,w)]^2\\J(w)=\frac{1}{2M}\sum^M_{t=1}[q_t^\lambda-\hat{Q}(S_t,A_t,w)]^2\label{td_target_replace}\end{equation}$$
如果存在对于预测问题最终基于摸一个策略最终价值函数$V_\pi(S)$或$Q_\pi(S,A)$或存在对于对于控制问题的最优价值函数$V_*(S)$或$Q_*(S,A)$分别记为$V_\textrm{target}$和$Q_\textrm{target}$，可以求目标价值的公式：
$$\begin{equation}J(w)=\frac{1}{2}\mathbb{E}[V_\textrm{target}(S)-\hat{V}(S,w)]^2\\J(w)=\frac{1}{2}\mathbb{E}[Q_\textrm{target}(S,A)-\hat{V}(S,A,w)]^2\label{v_appprox_target}\end{equation}$$
在实际求解w的过程中，将使用基于近似价值函数的目标值代替。
### 梯度下降使J(w)逼近w
一元函数的梯度就是其导数，多元函数在定义的空间内**可微**的$y=f(x_1,x_2,\cdots,x_n)$的梯度定义：
$$\nabla{y}=(\frac{\partial y}{\partial x_1},\cdots,\frac{\partial y}{\partial x_n})$$
梯度可以是一个向量。梯度的意义在于：**某一个位置沿着该位置梯度向量所指的方向时函数值增加最快的方向；反方向是减小最快的方向。**

为了求得最优的参数，所以使用迭代、梯度下降的方法求解：

1. 初始条件下设置参数$w=(w_1,w_2,\cdots,w_n)$值
2. 获取一个状态转换，带入目标函数J，并计算J对于各个参数的梯度：
$$\nabla_wJ(w)=
\begin{bmatrix}
\frac{\partial J(w)}{\partial w_1} \\
\vdots \\
\frac{\partial J(w)}{\partial w_n}
\end{bmatrix}
$$
3. 设置正的较小的学习率$\alpha$，将原参数w朝着梯度反方向做一定的更新：
$$\Delta w=-\alpha\nabla_wJ(w)\\w\gets w+\Delta w$$
4. 重复2,3直到参数w小于一个范围或者达到更新次数。

学习率不能太小，否则迭代的次数很多，太大就有可能导致在围绕真实值震荡。
### 常用的近似价值函数
#### 线性近似
线性价值函数使用一些列特征的线性组合来近似价值函数：
$$\hat{V}(S,w)=w^Tx(S)=\sum^n_{j=1}x_j(S)w_j$$
$x_j(S)$是状态S的第j个特征分量值，$w_j$是表示该特征分量的权重值，是求解的参数。基于公式$\eqref{v_appprox_target}$，对应的目标函数$J(w)$：
$$J(w)=\frac{1}{2}\mathbb{E}[V_\textrm{target}(S)-w^Tx(S)]^2$$
对应的梯度$\nabla_wJ(w)$：
$$\nabla_wJ(w)=-(V_\textrm{target}(S)-w^Tx(S))x(S)$$
参数的更新量$\Delta w$为：
$$\Delta w=\alpha(V_\textrm{target}(S)-w^Tx(S))x(S)$$
$V_\textrm{target}(S)$可以由不同的目标价值函数代替。
事实上查表的价值函数是线性近似价值函数的一个特例，只不过线性近似价值函数的特征树木就是所有的状态数目n，参数是由n个元素组成的向量：
$$\hat{V}(S,w)=
\begin{bmatrix}
1(S=s1) \\
\vdots \\
1(S=s_n)
\end{bmatrix}\cdot
\begin{bmatrix}
w_1\\
\vdots\\
w_n
\end{bmatrix}
$$
#### 神经网络
神经网络是一种非线性近似，他的基本单位是可以进行非线性变换的神经元。

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/value_function_approx/nn_approximation.png)
单个神经元在线性近似的基础上加了偏置项b和非线性整合函数$\sigma$，偏置项b可以被认为是一个额外的数据为1的输入项的权重。单个神经元最终的输出$\hat{y}$：
$$\hat{y}=\sigma(z)=\sigma(w^Tx+b)$$
$\sigma$称为神经元的**激活函数**，主要有ReLU和tanh。一般说来，在神经网络中隐藏层越多他能表达的对训练数据的非线性近似能力越强。设：

- $n^{[l]}$：第l层神经元数量
- $z^{[l]}_i$：第l层第i个神经元输入
- $a^{[l]}_i$：第l层第i个神经元输出。一般情况下$a^{[l]}_i=\sigma(z^{[l]}_i)$
- $w^{[l]}_{ji}$：第l层第j个神经元与第l-1层第i个神经元之间连接的权重
- $b^{[l]}_j$：第l层第j个神经元的偏置项

那么L层全连接神经网络，由：
$$<W^{[1]},b^{[1]}>,\cdots,<W^{[L]},b^{[L]}>$$
构成。其中$W^{[l]}$是一个$n^{[l]}\times n^{[l-1]}$的二维矩阵，$b^{[l]}$是由$n^{[l]}$个元素构成的一维列向量。网络中，第l层第j个元素的输出$a_j^{[l]}$为：
$$a_j^{[l]}=\sigma(z_j^{[l]})=\sigma(\sum_{i=1}^{n^{[l-1]}}w_{ji}^{[l]}+b_j^{[l]})$$
公式的矩阵形式：
$$a^{[l]}=\sigma(z^{[l]})=\sigma(W^{[l]}a^{[l]}+b^{[l]})$$
各个神经元层可以按顺序连接，前一层输出给后一层输入，直到输出层输出一个确定的数据。这种过程为**前向传播**。也可以里用梯度以及梯度下降法求解符合任务的参数。计算梯度的时候需要从神经网络的输出层开始逐层计算直至输入层，这种过程称为**反向传播**。目标函数计算神经网络根据输入数据计算得到的输出$\hat{y}$与实际期望输出y之间的误差，计算各个参数的梯度，朝着误差小的方向更新参数。**均方差(MSE)**和**交叉熵**是常用的目标函数：
$$
J(W,b)_{\textrm{MSE}}=\frac{1}{2M}\sum_{k=1}^M[y^{(k)}-\hat{y}^{(k)}]^2\\
J(W,b)_\textrm{cross_entropy}=-\frac{1}{M}\sum_{k=1}^M[y^{(k)}\ln\hat{y}^{(k)}-(1-y^{(k)})\ln(1-\hat{y}^{(k)})]
$$
M指更新一次参数对应的训练样本数量，$y^{(k)}$和$\hat{y}^{(k)}$分别表示第k个训练样本的真实输出和经神经网络计算得到的输出。
均方差一般用于输出的绝对值大于1等大范围内的数值，而加插上多用于[0,1]之间的数。

为了避免陷入局部最优解，随机梯度下降(SGD)会在每计算一个样本之后就更新网络参数。如果一次目标函数计算了训练集中所有m个样本，并以此基础更新参数：若m=M，则称为块梯度下降；若m<<M，则称为小块(mini-batch)梯度下降。
#### CNN近似
未完，待续
## DQN算法
深度Q学习(DQN)算法主要是经历回访来实现价值函数收敛。具体的做法：个体能记住既往的状态转换经历，对于每一个完整的状态序列里的每一次转换，依据当前状态$s_t$价值以$\epsilon$-贪婪策略选择一个行为$a_t$，执行该行为得到奖励$r_{t+1}$和下一个状态$s_{t+1}$，将得到的状态状态存储在记忆中，当记忆中存储的容量足够大时，随机i从记忆力提取一定数量的状态转换，用其中下一状态来计算当前状态的目标价值，使用公式$\eqref{td_target_replace}$(TD目标的替换)计算目标价值与网络输出价值之间的均方误差，使用小块梯度下降更新网络参数。具体的流程：

![](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/rlearning/value_function_approx/dqn_algorithm.png)
$\theta$代表近似价值函数的参数。该算法中的状态S都有特征$\phi(S)$表示。在每产生一个行为A并于实际环境交互后，个体都会进行一次学习并更新一次参数。更新参数时使用的目标价值：
$$Q_\textrm{target}(S_t,A_t)=R_t+\gamma\max Q(S^{'}_t,A^{'}_t;\theta^-)$$
$\theta^-$是上一个更新周期价值网络的参数。
DQN并不能保证一直收敛。使用双价值网络的DDQN被认为很好的解决了这个问题。
# 参考&引用
- [(知乎)David Silver强化学习公开课中文讲解及实践-第六讲](https://zhuanlan.zhihu.com/p/28223841)