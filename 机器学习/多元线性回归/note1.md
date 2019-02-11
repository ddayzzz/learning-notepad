# 多元线性回归
约定有m个例子，由n个属性。
-  假设：$h_\theta=\theta^Tx=\theta_0 x_0+\theta_1 x_1+\cdots+\theta_nx_n$
-  参数：n+1维的向量$\theta$
-  代价函数（来自最小二乘法）：$J(\theta)=\frac{1}{2m}\sum_{i=1}^m(h_\theta(x^{(i)})-y^{(i)})^2$

约定$\theta_0^{(i)}=1$
## 多元线性回归的梯度下降算法
对于$\theta$中每个分量，重复更新：

$$\theta_j\coloneqq\theta_j-\alpha\frac{\partial{J(\theta)}}{\partial{\theta_j}}$$
然后分母偏导数：

$$\theta_j\coloneqq\theta_j-\alpha\frac{1}{m}\sum^m_{i=1}(h_\theta(x^{(i)})-y^{(i)})x_j^{(i)}$$
## 特征缩放
对每个特征(属性)值除以属性的长度，例如属性1取值是[0,2000)，那么属性就除以2000即可。这样做的目的是保障目标函数$J(\theta)$的等值线不会窄而高（两个属性的情况）。尽快使其收敛到最小值。只需要保证所有的是属性的取值范围合理（不是太大或者太小）。
### 均值归一化（Mean normalization）
- 线性函数归一化(Min-Max scaling)
- 0均值标准化(Z-score standardization)：数据近似的符合高斯分布，那么使用：

$$z=\frac{x-\mu}{\sigma}$$。
其中$\mu$代表数据的均值，$\sigma$代表标准差
## 多项式回归
有些时候，假设的函数可能并不能很好地拟合数据。定义多项式：

$$h_\theta=\theta_0+\theta_1x_1+\cdots+\theta_nx_n$$

同时让一个特征$s$：$x_i=s^i\quad\forall i \in[1,n]$
## 正规方程
梯度下降算法使用迭代找到最小值。通过对目标方程各个分量求偏导数令其等于0也可以求出最小值地点。当然，在ML中不会遍历每一个分量地偏导数。

假设m个例子，n个特征地数据。

$$x^{(i)}=
\begin{bmatrix}
x_0^{(i)} \\
\vdots\\
x_n^{(i)}
\end{bmatrix}
\in\mathbb{R}^{n+1}
$$
设计矩阵

$$X=
\begin{bmatrix}
(x^{(1)})^T\\
(x^{(2)})^T\\
\vdots\\
(x^{(m)})^T
\end{bmatrix}
\in\mathbb{R}^{m\times{(n+1)}}
$$

向量

$$y=
\begin{bmatrix}
y^{(1)}\\
y^{(2)}\\
\vdots\\
y^{(m)}
\end{bmatrix}
\in\mathbb{R}^{m}$$
重新定义

$$\theta=(X^TX)^{-1}X^Ty$$。这个时候特征缩放就不必要了。
## 梯度下降和正规方程地优缺点
见讲义