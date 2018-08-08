# 编译 ffmpeg 以及解码器
### 编译各种解码器
1. **yasm 汇编工具** 使用 `yum install yasm-devel yasm` 直接安装对应版本系统的二进制文件
2. **x264** 这个是提供 H264 编码的库，[下载源码](http://download.videolan.org/x264/snapshots/)。然后使用`./configure --prefix=/usr/local/ffmpeg --enable-shared --enable-static --enable-yasm`。在这里为了省事，直接把编译好的动态链接可以放入待会会安装的 ffmpeg 的目录中。注意一定要使用开启`--enanble-shared` 这个选项否则就会在 ffmpeg 编译的过程中发生链接错误(`can not be used when making a shared object; recompile with -fPIC`)！
3. **x265** [下载1.9版本的 h265 源码](https://bitbucket.org/multicoreware/x265/downloads/x265_1.9.tar.gz) 注意 x265 使用的 cmake 所以需要安装好 cmake。进入目录 `build/linux` 运行 `PATH="/usr/local/bin:$PATH" cmake -G "Unix Makefiles" -DCMAKE_INSTALL_PREFIX="/usr/local/ffmpeg" -DENABLE_SHARED:bool=off ../../source` 就可以编译好动态链接库版本的 x265 编码器。
4. **libvpx** 这个提供 WebM 的编码器，[下载源码](http://storage.googleapis.com/downloads.webmproject.org/releases/webm/libvpx-1.5.0.tar.bz2) 然后进入目录，`PATH="/usr/local/bin:$PATH" ./configure --prefix="/usr/local/ffmpeg" --disable-examples --disable-unit-tests` 进行编译和安装。
5. **fdk-acc** ACC 编码，[下载源码](https://jaist.dl.sourceforge.net/project/opencore-amr/fdk-aac/fdk-aac-0.1.5.tar.gz)， 不要使用 0.1.4 的版本。首先安装 `automake, automake-devel 和 libtool`。然后运行 `autoreconf -fiv;./configure --prefix="/usr/local/ffmpeg" --enable-shared`。
6. 这些编码仅仅是用来处理视频的，声音还要另外安装。这里就不多说了。现在编译 ffmpeg。。configure 使用如下的一些参数：`--enable-gpl`:使用 GPL 协议;`--pkg-config-flags`: pkgconfig(Makefile 的一个帮助工具，用于保存第三方引用链接的信息);`--enable-xxx`:编译 xxx 编码器。总的来说就是`./configure --prefix=/usr/local/ffmpeg --pkg-config-flags="--static" --enable-shared --enable-yasm --enable-libx264 --enable-gpl --enable-pthreads --bindir="/usr/local/bin" --enable-libfdk-aac --enable-libvpx --enable-libx264 --enable-libx265 --enable-nonfree`。为了编译为动态链接库的版本，还需要使用 `make CFLAGS='-fPIC'`。最后安装即可，生成的可执行文件在`/usr/bin` 下（--bindir 指定）。
### 注意事项
- 编译 ffmpeg 的时候找不到 x265 的 pkgconfig ，则需要因把 `/usr/local/ffmpeg/lib/pkgconfig` 添加到变量 PKG_CONFIG_PATH 中，例如：`/usr/local/x264/lib/pkgconfig:/usr/local/ffmpeg/lib/pkgconfig:/usr/lib64/pkgconfig`，然后 export 到 shell 中。
- 找不到链接库，同上一篇文章，把 `/usr/local/ffmpeg/lib` 添加到 ld.so.conf 中，然后 sudo ldconfig。
- 编译的时候找不到头文件：（有 x264 是因为之前 x264 并没有编译到 ffmpeg 中）
```
C_INCLUDE_PATH=$C_INCLUDE_PATH:/usr/local/ffmpeg/include:/usr/local/x264/include
CPLUS_INCLUDE_PATH=$CPLUS_INCLUDE_PATH:/usr/local/ffmpeg/include:/usr/local/x264/include
export C_INCLUDE_PATH
export CPLUS_INCLUDE_PATH
LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/x264/lib:/usr/local/ffmpeg/lib
export LD_LIBRARY_PATH
```
## 题外话：虚拟机中的 ffmpeg 输出流到实体机
在 Linux 下可以借助 ffserver 响应用户的请求。
![ffserver 原理](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/memo-ffserver-principle.png)
为了能够使主机与虚拟机相互 ping 通，可以使用虚拟机的桥接模式将虚拟机和主机放置在一个子网之中。
#### 将虚拟机与主机放置在一个子网中：
1. 添加一个环回网卡。在计算机管理MMC中，点击操作然后添加红框中的网络适配器设备
![添加网卡](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/vm-addloop-net-adapter.png)
2. 管理员打开 VMware 的虚拟网络编辑器（这是一个例子），找到 VMnet 0，在桥接模式的下拉列表中选择刚刚那个网卡并确定。
3. 打开那个网卡的 IPv4 的设置，输入一个指定的主机 IP地址、网关和子网掩码：
![Windows IP 设置](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/vm-loop-host-setting.png)
4. 这样主机的 IP 地址为 192.168.100.101，网关是 192.168.100.100（用来指定将数据发送到那个网卡上）。同样需要设置虚拟机的 IP 地址，保持子网掩码和网关与主机一直，IP 地址只要在 192.168.100. 子网内就可以了：
![Linux 虚拟机 IP 设置](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/vm-loop-vm-setting.png)
5. 主机和虚拟机的 IP 分别为 192.168.100.101 和 192.168.100.102。但是如果需要访问各自的服务，一个不好的方法是关闭二者都有的防火墙。
Fedora:
```
systemctl stop firewalld.service
systemctl disable firewalld.service
```
这样就可以 ping 通：![ping 通](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/vm-host-ping-connected.png)
#### 搭建 ffserver
![ffserver 广播](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/memo-ffserver-feed-principle.png)
ffserver 默认的配置文件在 `/etc/ffserver.conf`，通过开关 -f 也可以指定其他的。ffserver 需要:
- 绑定一个监听的端口这样可以监听远程的访问
- 添加一个输出配置，用于指定广播流的格式
- 指定本地由 ffmepg 传送到的 ffm 的定义以及关联的（feed）输出配置
`/etc/ffserver.conf`:
```
HTTPPort 8090                      # Port to bind the server to
HTTPBindAddress 0.0.0.0
MaxHTTPConnections 2000
MaxClients 1000
MaxBandwidth 10000             # Maximum bandwidth per client
                               # set this high enough to exceed stream bitrate
CustomLog -
NoDaemon                       # Remove this if you want FFserver to daemonize after start

<Feed feed1.ffm>               # This is the input feed where FFmpeg will send
   File /tmp/feed1.ffm            # video stream.
   FileMaxSize 64M              # Maximum file size for buffering video
   ACL allow 127.0.0.1         # Allowed IPs
</Feed>

<Stream test.webm>              # Output stream URL definition
   Feed feed1.ffm              # Feed from which to receive video
   Format webm

   # Audio settings
   #AudioCodec vorbis
   #AudioBitRate 64             # Audio bitrate

   # Video settings
   VideoCodec libvpx
   VideoSize 320x240           # Video resolution
   VideoFrameRate 30           # Video FPS
   AVOptionVideo flags +global_header  # Parameters passed to encoder
                                       # (same as ffmpeg command-line parameters)
   AVOptionVideo cpu-used 0
   AVOptionVideo qmin 10
   AVOptionVideo qmax 42
   AVOptionVideo quality good
   NoAudio
   #PreRoll 15
   #StartSendOnKey
   VideoBitRate 128          # Video bitrate
</Stream>

<Stream status.html>            # Server status URL
   Format status
   # Only allow local people to get the status
   ACL allow localhost
   ACL allow 192.168.0.0 192.168.255.255
</Stream>

<Redirect index.html>    # Just an URL redirect for index
   # Redirect index.html to the appropriate site
   URL http://www.ffmpeg.org/
</Redirect>
```
由于不需要传输声音所以我没有指定声音的编码。定义了一下的信息
- 客户通过：http://ip:8090/status.html 可以获得当前 ffserver 的运行信息
![ffserver 信息](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/vm-host-ffserver-status.png)
- 输入源输出到本地的广播源：http://127.0.0.1/feed1.ffm
- 客户可以通过：http://ip:8090/test.webm 访问这个流。
#### ffmpeg 命令行输出到广播源
- 输出一段视频：`ffmpeg  -i ~/ai.mp4 -r 30 http://127.0.0.1:8090/feed1.ffm`
- 输出摄像头（虚拟机不能使用）：`ffmpeg -f video4linux2 -s 320x240 -r 30 -i /dev/video0  http://127.0.0.1:8090/feed1.ffm`
![输出的视频](https://ddayzzz-blog.oss-cn-shenzhen.aliyuncs.com/articles/memo-system/vm-host-ffserver-connected.png)
#### 使用 ffmpeg 对流进行处理
- 保存为本地的视频文件：`ffmpeg -i http://192.168.100.102:8090/test.webm save.mp4`